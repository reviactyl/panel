#!/usr/bin/env python3

import argparse
import hashlib
import json
import os
import subprocess
import sys
from datetime import datetime, timedelta, timezone


IMAGES = (
    "mariadb:10",
    "mariadb:11",
    "mysql:8",
    "mysql:9",
    "postgres:17",
    "postgres:18",
)
MIRROR = "ghcr.io/reviactyl/panel-ci-databases"


def mirror_is_fresh():
    endpoint = (
        "repos/reviactyl/panel/actions/workflows/database-image-mirror.yml/runs"
        "?branch=develop&status=success&per_page=1"
    )
    try:
        result = subprocess.run(
            ["gh", "api", endpoint], check=True, capture_output=True, timeout=30
        )
        runs = json.loads(result.stdout)["workflow_runs"]
        if runs:
            refreshed = datetime.fromisoformat(
                runs[0]["updated_at"].replace("Z", "+00:00")
            )
            age = datetime.now(timezone.utc) - refreshed
            return timedelta() <= age <= timedelta(hours=48)
    except (subprocess.SubprocessError, KeyError, ValueError):
        pass
    return False


def mirror_tag(image):
    return f"{MIRROR}:{image.replace(':', '-')}"


def manifest_digest(image, anonymous=False, timeout=180):
    command = ["skopeo", "inspect", "--raw", "--retry-times", "3"]
    if anonymous:
        command.append("--no-creds")
    result = subprocess.run(
        [*command, f"docker://{image}"],
        check=True,
        capture_output=True,
        timeout=timeout,
    )
    manifest = json.loads(result.stdout)
    if manifest.get("schemaVersion") != 2:
        raise ValueError(f"Unsupported image manifest for {image}")
    return "sha256:" + hashlib.sha256(result.stdout).hexdigest()


def resolve_images(require_mirror=False, use_mirror=True):
    if not use_mirror:
        if require_mirror:
            raise ValueError(
                "No successful database image refresh within 48 hours; run Database image mirror."
            )
        print(
            "::warning::No recent verified database image refresh; using Docker Hub. "
            "Run Database image mirror before enabling required mirrors.",
            file=sys.stderr,
        )
        return dict(zip(IMAGES, IMAGES))
    images = {}
    for image in IMAGES:
        try:
            digest = manifest_digest(mirror_tag(image), anonymous=True, timeout=30)
        except subprocess.SubprocessError:
            if require_mirror:
                raise
            print(
                f"::warning::Mirror unavailable for {image}; using Docker Hub. "
                "Run the Database image mirror workflow and make its GHCR package public.",
                file=sys.stderr,
            )
            images[image] = image
        else:
            images[image] = f"{MIRROR}@{digest}"
    return images


def mirror_images():
    images = {}
    for image in IMAGES:
        source = f"docker.io/library/{image}"
        digest = manifest_digest(source)
        repository = source.rsplit(":", 1)[0]
        target = mirror_tag(image)
        print(f"Mirroring {image}@{digest}", file=sys.stderr)
        subprocess.run(
            [
                "skopeo",
                "copy",
                "--all",
                "--preserve-digests",
                "--retry-times",
                "3",
                f"docker://{repository}@{digest}",
                f"docker://{target}",
            ],
            check=True,
            stdout=sys.stderr,
            timeout=600,
        )
        if manifest_digest(target, anonymous=True) != digest:
            raise ValueError(f"Mirror digest does not match upstream for {image}")
        images[image] = f"{MIRROR}@{digest}"
        if summary := os.environ.get("GITHUB_STEP_SUMMARY"):
            with open(summary, "a", encoding="utf-8") as output:
                output.write(f"- `{image}`: verified public mirror `{images[image]}`\n")
    return images


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("mode", choices=("resolve", "mirror"))
    args = parser.parse_args()
    try:
        if args.mode == "mirror":
            images = mirror_images()
        else:
            images = resolve_images(
                require_mirror=os.environ.get(
                    "CI_DATABASE_MIRROR_REQUIRED", "false"
                ).lower()
                == "true",
                use_mirror=mirror_is_fresh(),
            )
    except (subprocess.SubprocessError, ValueError) as error:
        print(f"::error::{error}", file=sys.stderr)
        if isinstance(error, subprocess.CalledProcessError) and error.stderr:
            print(error.stderr.decode(errors="replace"), file=sys.stderr)
        return 1
    print(json.dumps(images, separators=(",", ":")))
    return 0


if __name__ == "__main__":
    sys.exit(main())
