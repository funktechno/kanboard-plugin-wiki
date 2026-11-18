#!/usr/bin/env bash
# Prints the plugin version for HEAD (details on stderr): a release tag on HEAD wins, otherwise the rules in
# versionRules.sh apply. Preview another branch with VERSION_BRANCH=dev. PowerShell twin: generateVersion.ps1.
set -euo pipefail

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO"
source "$REPO/scripts/version/versionRules.sh"

# Branch: override, else the CI branch, else the local one.
GIT_BRANCH="${VERSION_BRANCH:-${GITHUB_HEAD_REF:-}}"
if [ -z "$GIT_BRANCH" ] && [ "${GITHUB_REF_TYPE:-}" = "branch" ]; then GIT_BRANCH="$GITHUB_REF_NAME"; fi
GIT_BRANCH="${GIT_BRANCH:-$(git rev-parse --abbrev-ref HEAD)}"

stable_tags() { grep -E '^v?[0-9]+\.[0-9]+\.[0-9]+$' || true; }
EXACT_TAG=$(git tag --points-at HEAD | stable_tags | sort -V | tail -n1)
LATEST_TAG=$(git tag --merged HEAD --sort=-v:refname | stable_tags | head -n1)

MAJOR_MINOR_PATCH="${LATEST_TAG#v}"
MAJOR_MINOR_PATCH="${MAJOR_MINOR_PATCH:-0.0.0}"
if [ -n "$LATEST_TAG" ]; then
  COMMIT_COUNT=$(git rev-list --count "$LATEST_TAG..HEAD")
else
  COMMIT_COUNT=$(git rev-list --count HEAD)
fi

if [ -n "$EXACT_TAG" ]; then
  VERSION="${EXACT_TAG#v}"
else
  VERSION="$MAJOR_MINOR_PATCH"
  apply_version_rules "$GIT_BRANCH"
fi

echo "branch=$GIT_BRANCH latest_tag=${LATEST_TAG:-none} exact_tag=${EXACT_TAG:-none} version=$VERSION" >&2
echo "$VERSION"
