# Release Process

This plugin uses GitHub releases for automatic updates. Here's how to create a new release:

## Prerequisites

1. All changes committed and pushed to the `main` branch
2. Version number updated in `seo-friendly-jupiter-pagination.php` (line 6)
3. Version number updated in `readme.txt` (Stable tag)
4. Changelog updated in both `readme.txt` and `README.md`

## Creating a Release

### Via GitHub Web Interface

1. Go to https://github.com/markfenske84/seo-friendly-jupiter-pagination/releases
2. Click "Draft a new release"
3. Click "Choose a tag" and create a new tag:
   - Tag format: `v1.4.1` (must match version in plugin file)
   - Example: `v1.4.1`, `v1.4.2`, `v2.0.0`
4. Set the release title (e.g., "Version 1.4.1")
5. Add release notes (copy from CHANGELOG)
6. Click "Publish release"

### Via Command Line

```bash
# Make sure you're on main branch
git checkout main

# Tag the release (replace 1.4.1 with your version)
git tag -a v1.4.1 -m "Version 1.4.1"

# Push the tag to GitHub
git push origin v1.4.1

# Then create the release on GitHub web interface
```

## After Release

1. WordPress sites using the plugin will automatically see an update notification
2. Users can update directly from their WordPress admin panel
3. Updates are pulled from the latest GitHub release

## Version Numbering

Use Semantic Versioning (SemVer):
- **MAJOR** version (1.x.x): Incompatible API changes
- **MINOR** version (x.1.x): Add functionality (backwards compatible)
- **PATCH** version (x.x.1): Bug fixes (backwards compatible)

Examples:
- Bug fixes: `1.4.1` → `1.4.2`
- New features: `1.4.2` → `1.5.0`
- Breaking changes: `1.5.0` → `2.0.0`

## Testing Before Release

1. Test on a staging/local WordPress site
2. Verify all features work as expected
3. Check for PHP errors in debug.log
4. Test with latest WordPress version
5. Test with Jupiter theme

## Checklist

- [ ] Version updated in `seo-friendly-jupiter-pagination.php`
- [ ] Stable tag updated in `readme.txt`
- [ ] Changelog updated in `readme.txt`
- [ ] Changelog updated in `README.md`
- [ ] All changes committed and pushed
- [ ] Git tag created and pushed
- [ ] GitHub release published
- [ ] Tested update process on a WordPress site

