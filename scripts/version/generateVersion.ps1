# Prints the plugin version for HEAD; PowerShell twin of generateVersion.sh, rules in versionRules.sh.
# Preview another branch with $env:VERSION_BRANCH = 'dev'.

$ErrorActionPreference = 'Stop'
Push-Location (Resolve-Path "$PSScriptRoot/../..")
try {
    function Get-StableTags([string[]]$tags) { $tags | Where-Object { $_ -match '^v?\d+\.\d+\.\d+$' } }

    # Mirrors release_line_version in versionRules.sh
    function Get-ReleaseLineVersion([string]$ref) {
        $tag = Get-StableTags (git tag --merged $ref --sort=-v:refname) | Select-Object -First 1
        if (-not $tag) { return $null }
        if ((git rev-parse "$tag^{commit}") -eq (git rev-parse "$ref^{commit}")) { return ($tag -replace '^v', '') }
        $parts = ($tag -replace '^v', '').Split('.')
        $merges = [Math]::Max([int](git rev-list --count --first-parent --merges "$tag..$ref"), 1)
        return "$($parts[0]).$($parts[1]).$([int]$parts[2] + $merges)"
    }

    $branch = if ($env:VERSION_BRANCH) { $env:VERSION_BRANCH }
              elseif ($env:GITHUB_HEAD_REF) { $env:GITHUB_HEAD_REF }
              elseif ($env:GITHUB_REF_TYPE -eq 'branch') { $env:GITHUB_REF_NAME }
              else { git rev-parse --abbrev-ref HEAD }

    $exactTag = Get-StableTags (git tag --points-at HEAD) | Sort-Object { [version]($_ -replace '^v', '') } | Select-Object -Last 1
    $latestTag = Get-StableTags (git tag --merged HEAD --sort=-v:refname) | Select-Object -First 1
    $majorMinorPatch = if ($latestTag) { $latestTag -replace '^v', '' } else { '0.0.0' }
    $commitCount = if ($latestTag) { [int](git rev-list --count "$latestTag..HEAD") } else { [int](git rev-list --count HEAD) }

    if ($exactTag) {
        $version = $exactTag -replace '^v', ''
    } elseif ($branch -in 'dev', 'main', 'master') {
        $line = Get-ReleaseLineVersion 'HEAD'
        $version = if ($line) { $line } else { "$majorMinorPatch-$commitCount" }
    } else {
        $target = if ($env:VERSION_TARGET) { $env:VERSION_TARGET } elseif ($env:GITHUB_BASE_REF) { $env:GITHUB_BASE_REF } else { 'dev' }
        $targetRef = $null
        foreach ($candidate in "origin/$target", $target) {
            git rev-parse -q --verify "$candidate^{commit}" 2>$null | Out-Null
            if ($LASTEXITCODE -eq 0) { $targetRef = $candidate; break }
        }
        $base = if ($targetRef) { Get-ReleaseLineVersion $targetRef } else { $null }
        if ($base) {
            $version = "$base-$([int](git rev-list --count --no-merges "$targetRef..HEAD"))"
        } else {
            $version = "$majorMinorPatch-$commitCount"
        }
        $version = $version -replace '-0$', ''
    }

    Write-Host "branch=$branch latest_tag=$(if ($latestTag) { $latestTag } else { 'none' }) exact_tag=$(if ($exactTag) { $exactTag } else { 'none' }) version=$version"
    Write-Output $version
} finally {
    Pop-Location
}
