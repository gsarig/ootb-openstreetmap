# Build Scripts

## `build-zip.sh`

This script creates a production-ready zip file of the plugin, mirroring the GitHub Release workflow.

### Usage

**Option 1: Auto-detect version**
Runs the script using the version defined in `ootb-openstreetmap.php`.
```bash
./bin/build-zip.sh
```

**Option 2: Manual version**
Creates a zip with a specific version number.
```bash
./bin/build-zip.sh 1.2.3
```

### Details
- The script uses `rsync` to create a clean copy of the plugin.
- It respects `.distignore` to exclude development files (like `node_modules`, `.git`, `/bin`).
- **Important**: It zips the **current state** of your files on disk, including uncommitted changes.
