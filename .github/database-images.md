# Database image mirrors

The Tests workflow uses public copies of the official MariaDB 10/11, MySQL 8/9,
and PostgreSQL 17/18 images in `ghcr.io/reviactyl/panel-ci-databases`. The images
are copied without rebuilding, including every platform, and the source and
destination manifest digests must match. Each test run resolves the public tags
to immutable digests before starting its service containers. All eight database
test combinations still run against fresh containers.

The Database image mirror workflow refreshes the six upstream tags daily and
can also run manually. It uses the existing `DOCKER_USER` variable and
`DOCKER_PAT` secret when configured; only the trusted mirror workflow has
package write permission. A failed copy, digest mismatch, or anonymous access
check fails the refresh workflow. Refresh failures need attention: existing
mirrors remain available, but may miss upstream updates until a refresh succeeds.
Preflight requires a successful refresh on official `develop` within the last
48 hours. Older or unverified mirrors produce a warning and a Docker Hub
fallback during bootstrap, or fail preflight when mirrors are required.

## Initial setup

1. After merging, the mirror workflow runs automatically. GHCR initially creates
   packages as private, so its anonymous access check can fail on the first run.
2. Set the `panel-ci-databases` package's visibility to **Public** in the
   Reviactyl organization's package settings, then rerun the workflow.
3. Confirm its summary lists six verified public mirrors. The next Tests run
   automatically uses their immutable GHCR references, including for fork PRs.
4. Set the repository variable `CI_DATABASE_MIRROR_REQUIRED` to `true` to make
   missing or inaccessible mirrors fail preflight instead of falling back.

Before mirrors are ready, preflight warns and uses the original Docker Hub
tags and credentials for unavailable images. This keeps the initial
rollout and standalone forks working; it is still subject to Docker Hub limits.
Each preflight image lookup has a 30-second timeout, so even six stalled
mirrors return their fallbacks within three minutes of registry checks.
When `CI_DATABASE_MIRROR_REQUIRED=true`, no Docker Hub fallback is allowed,
including when the latest successful refresh is over 48 hours old.

Healthy daily refreshes normally pick up upstream changes within 24 hours.
Preflight permits a refresh age of up to 48 hours to tolerate delayed runs.
To pick up an urgent upstream fix, run Database image mirror manually before
rerunning Tests. No database contents or test results are cached, and these
images do not consume the repository's Actions cache allowance.

Local checks:

```sh
python3 -m unittest discover -s .github/scripts -p 'test_*.py'
actionlint .github/workflows/tests.yml .github/workflows/database-image-mirror.yml
```
