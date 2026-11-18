#!/usr/bin/env bash
# Version rules, sourced by generateVersion.sh (generateVersion.ps1 mirrors them): dev/main/master get the
# newest X.Y.Z tag with its patch raised by the merges since it (0.5.0 + 3 merges -> 0.5.3); other branches
# get the target branch's version with its patch raised by their non-merge commits ahead of it (0.5.3 + 2 ->
# 0.5.5). The target is $VERSION_TARGET, else $GITHUB_BASE_REF, else dev. Needs full history and tags.

release_line_version() {
  local ref="$1" base_tag tag_major tag_minor tag_patch merges
  base_tag=$(git tag --merged "$ref" --sort=-v:refname | grep -E '^v?[0-9]+\.[0-9]+\.[0-9]+$' | head -n1 || true)
  [ -n "$base_tag" ] || return 0
  if [ "$(git rev-parse "$base_tag^{commit}")" = "$(git rev-parse "$ref^{commit}")" ]; then
    echo "${base_tag#v}"
    return 0
  fi
  IFS=. read -r tag_major tag_minor tag_patch <<< "${base_tag#v}"
  merges=$(git rev-list --count --first-parent --merges "$base_tag..$ref")
  if [ "$merges" -lt 1 ]; then merges=1; fi
  echo "$tag_major.$tag_minor.$((tag_patch + merges))"
}

apply_version_rules() {
  local branch="${1:-}" target target_ref="" candidate base ahead

  case "$branch" in
    dev|main|master)
      base=$(release_line_version HEAD)
      [ -n "$base" ] || return 0
      MAJOR_MINOR_PATCH="$base"
      VERSION="$base"
      ;;
    *)
      target="${VERSION_TARGET:-${GITHUB_BASE_REF:-dev}}"
      for candidate in "origin/$target" "$target"; do
        if git rev-parse -q --verify "$candidate^{commit}" >/dev/null 2>&1; then target_ref="$candidate"; break; fi
      done
      base=""
      if [ -n "$target_ref" ]; then base=$(release_line_version "$target_ref"); fi

      if [ -n "$base" ]; then
        ahead=$(git rev-list --count --no-merges "$target_ref..HEAD")
      else
        base="$MAJOR_MINOR_PATCH" ahead="$COMMIT_COUNT"
      fi
      MAJOR_MINOR_PATCH="${base%.*}.$(( ${base##*.} + ahead ))"
      VERSION="$MAJOR_MINOR_PATCH"
      ;;
  esac
}
