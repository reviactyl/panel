import hashlib
import io
import json
import os
import subprocess
import tempfile
import unittest
from pathlib import Path
from datetime import datetime, timedelta, timezone
from unittest.mock import patch

import database_images


class DatabaseImagesTest(unittest.TestCase):
    def test_only_a_recent_successful_refresh_is_accepted(self):
        for hours, expected in [(1, True), (47, True), (49, False), (-1, False)]:
            with (
                self.subTest(hours=hours),
                patch("database_images.subprocess.run") as run,
            ):
                refreshed = datetime.now(timezone.utc) - timedelta(hours=hours)
                run.return_value.stdout = json.dumps(
                    {"workflow_runs": [{"updated_at": refreshed.isoformat()}]}
                ).encode()
                self.assertEqual(database_images.mirror_is_fresh(), expected)
                self.assertIn(
                    "branch=develop&status=success", run.call_args.args[0][-1]
                )

    def test_missing_refresh_or_api_failure_cannot_mark_a_mirror_fresh(self):
        with patch("database_images.subprocess.run") as run:
            run.return_value.stdout = b'{"workflow_runs":[]}'
            self.assertFalse(database_images.mirror_is_fresh())
            run.side_effect = subprocess.CalledProcessError(1, "gh")
            self.assertFalse(database_images.mirror_is_fresh())

    def test_stale_mirrors_warn_and_use_hub_without_resolving_old_tags(self):
        with (
            patch("database_images.manifest_digest") as inspect,
            patch("sys.stderr", new_callable=io.StringIO) as log,
        ):
            images = database_images.resolve_images(use_mirror=False)
        self.assertEqual(
            images, dict(zip(database_images.IMAGES, database_images.IMAGES))
        )
        inspect.assert_not_called()
        self.assertIn("::warning::No recent verified", log.getvalue())

    def test_required_mirror_rejects_stale_refresh(self):
        with self.assertRaisesRegex(ValueError, "within 48 hours"):
            database_images.resolve_images(require_mirror=True, use_mirror=False)

    def test_digest_uses_the_unchanged_manifest_and_anonymous_access(self):
        manifest = (
            b'{"schemaVersion":2,"manifests":[{"platform":{"architecture":"arm64"}}]}'
        )
        with patch("database_images.subprocess.run") as run:
            run.return_value.stdout = manifest
            digest = database_images.manifest_digest(
                "ghcr.io/example/database:10", anonymous=True
            )
        self.assertEqual(digest, "sha256:" + hashlib.sha256(manifest).hexdigest())
        self.assertIn("--no-creds", run.call_args.args[0])
        self.assertIn("--raw", run.call_args.args[0])
        self.assertEqual(
            run.call_args.args[0][-1], "docker://ghcr.io/example/database:10"
        )

    def test_unsupported_manifest_fails_instead_of_using_its_hash(self):
        with patch("database_images.subprocess.run") as run:
            run.return_value.stdout = b'{"schemaVersion":1}'
            with self.assertRaisesRegex(ValueError, "Unsupported image manifest"):
                database_images.manifest_digest("ghcr.io/example/database:10")

    def test_all_database_versions_resolve_to_public_immutable_images(self):
        with patch(
            "database_images.manifest_digest", return_value="sha256:" + "a" * 64
        ) as inspect:
            images = database_images.resolve_images()
        self.assertEqual(set(images), set(database_images.IMAGES))
        self.assertEqual(len(images), 6)
        for image, reference in images.items():
            self.assertEqual(reference, database_images.MIRROR + "@sha256:" + "a" * 64)
            inspect.assert_any_call(
                database_images.mirror_tag(image), anonymous=True, timeout=30
            )

    def test_bootstrap_falls_back_only_for_the_unavailable_image_and_warns(self):
        def inspect(image, anonymous, timeout):
            if image.endswith(":mysql-8"):
                raise subprocess.CalledProcessError(
                    1, "skopeo", stderr=b"manifest unknown"
                )
            return "sha256:" + "a" * 64

        with (
            patch("database_images.manifest_digest", side_effect=inspect),
            patch("sys.stderr", new_callable=io.StringIO) as log,
        ):
            images = database_images.resolve_images()
        self.assertEqual(images["mysql:8"], "mysql:8")
        self.assertTrue(images["mysql:9"].startswith("ghcr.io/"))
        self.assertEqual(log.getvalue().count("::warning::"), 1)
        self.assertIn("mysql:8", log.getvalue())

    def test_required_mirror_never_falls_back_on_registry_failure(self):
        with patch(
            "database_images.manifest_digest",
            side_effect=subprocess.CalledProcessError(1, "skopeo"),
        ):
            with self.assertRaises(subprocess.CalledProcessError):
                database_images.resolve_images(require_mirror=True)

    def test_all_stalled_mirrors_fall_back_within_the_preflight_budget(self):
        def stall(command, **options):
            raise subprocess.TimeoutExpired(command, options["timeout"])

        with (
            patch("database_images.subprocess.run", side_effect=stall) as run,
            patch("sys.stderr", new_callable=io.StringIO) as log,
        ):
            images = database_images.resolve_images()

        self.assertEqual(
            images, dict(zip(database_images.IMAGES, database_images.IMAGES))
        )
        self.assertEqual(run.call_count, 6)
        self.assertLessEqual(
            sum(call.kwargs["timeout"] for call in run.call_args_list), 180
        )
        self.assertEqual(log.getvalue().count("::warning::"), 6)

    def test_required_mirror_stops_on_the_first_stalled_registry(self):
        with patch(
            "database_images.subprocess.run",
            side_effect=subprocess.TimeoutExpired("skopeo", 30),
        ) as run:
            with self.assertRaises(subprocess.TimeoutExpired):
                database_images.resolve_images(require_mirror=True)
        self.assertEqual(run.call_count, 1)

    def test_refresh_copies_current_upstream_digest_and_all_platforms(self):
        digest = "sha256:" + "b" * 64
        with (
            patch.object(database_images, "IMAGES", ("mysql:8",)),
            patch("database_images.manifest_digest", return_value=digest) as inspect,
            patch("database_images.subprocess.run") as copy,
            patch.dict(os.environ, {"GITHUB_STEP_SUMMARY": ""}),
        ):
            images = database_images.mirror_images()
        self.assertEqual(inspect.call_args_list[0].args, ("docker.io/library/mysql:8",))
        self.assertEqual(inspect.call_args_list[1].kwargs, {"anonymous": True})
        self.assertEqual(
            inspect.call_args_list[1].args, (database_images.MIRROR + ":mysql-8",)
        )
        command = copy.call_args.args[0]
        self.assertIn("--all", command)
        self.assertIn("--preserve-digests", command)
        self.assertIn("docker://docker.io/library/mysql@" + digest, command)
        self.assertEqual(command[-1], "docker://" + database_images.MIRROR + ":mysql-8")
        self.assertEqual(images["mysql:8"], database_images.MIRROR + "@" + digest)

    def test_digest_mismatch_fails_without_reporting_a_verified_mirror(self):
        with tempfile.TemporaryDirectory() as directory:
            summary = Path(directory) / "summary.md"
            with (
                patch.object(database_images, "IMAGES", ("mysql:8",)),
                patch(
                    "database_images.manifest_digest",
                    side_effect=["sha256:" + "a" * 64, "sha256:" + "b" * 64],
                ),
                patch("database_images.subprocess.run"),
                patch.dict(os.environ, {"GITHUB_STEP_SUMMARY": str(summary)}),
            ):
                with self.assertRaisesRegex(ValueError, "does not match upstream"):
                    database_images.mirror_images()
            self.assertFalse(summary.exists())

    def test_private_mirror_cannot_be_reported_as_verified(self):
        with (
            patch.object(database_images, "IMAGES", ("mysql:8",)),
            patch(
                "database_images.manifest_digest",
                side_effect=[
                    "sha256:" + "a" * 64,
                    subprocess.CalledProcessError(1, "skopeo"),
                ],
            ),
            patch("database_images.subprocess.run"),
        ):
            with self.assertRaises(subprocess.CalledProcessError):
                database_images.mirror_images()

    def test_copy_failure_stops_refresh_before_verification(self):
        with (
            patch.object(database_images, "IMAGES", ("mysql:8",)),
            patch(
                "database_images.manifest_digest", return_value="sha256:" + "a" * 64
            ) as inspect,
            patch(
                "database_images.subprocess.run",
                side_effect=subprocess.CalledProcessError(1, "skopeo"),
            ),
        ):
            with self.assertRaises(subprocess.CalledProcessError):
                database_images.mirror_images()
            self.assertEqual(inspect.call_count, 1)

    def test_cli_propagates_required_mirror_failure(self):
        with (
            patch("sys.argv", ["database_images.py", "resolve"]),
            patch.dict(os.environ, {"CI_DATABASE_MIRROR_REQUIRED": "true"}),
            patch("database_images.mirror_is_fresh", return_value=True),
            patch(
                "database_images.manifest_digest",
                side_effect=subprocess.CalledProcessError(
                    1, "skopeo", stderr=b"access denied"
                ),
            ),
            patch("sys.stdout", new_callable=io.StringIO) as output,
            patch("sys.stderr", new_callable=io.StringIO) as error,
        ):
            self.assertEqual(database_images.main(), 1)
        self.assertEqual(output.getvalue(), "")
        self.assertIn("access denied", error.getvalue())


if __name__ == "__main__":
    unittest.main()
