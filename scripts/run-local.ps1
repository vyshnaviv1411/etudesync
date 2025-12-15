<#
Run this script from an elevated PowerShell prompt to prepare and open the app:

Usage examples:
  # Run with defaults
  .\scripts\run-local.ps1

  # Specify custom MySQL password and XAMPP location
  .\scripts\run-local.ps1 -DBPass "mypassword" -XAMPPPath "C:\\xampp"

What it does (best-effort):
- Attempts to start XAMPP services (via xampp_start.exe or service names)
- Creates uploads folder `assets/uploads/room_files` and grants Modify to current user
- Imports `sql/etudesync_schema.sql` into MySQL (prompts for password if not provided)
- Opens `http://localhost/etudesync/public/` in default browser

Note: This script cannot start services if your system uses nonstandard service names
or if XAMPP is installed in a different location — supply the correct paths via parameters.
#>

[CmdletBinding()]
param(
    [string]$XAMPPPath = "C:\\xampp",
    [string]$ProjectPath = "C:\\xampp\\htdocs\\vysh_edu\\etudesync",
    [string]$DBName = "etudesync",
    [string]$DBUser = "root",
    [string]$DBPass = ""
)

function Start-XamppIfPossible {
    Write-Host "Attempting to start XAMPP services (best-effort)..."

    $xamppStart = Join-Path $XAMPPPath 'xampp_start.exe'
    $xamppBat = Join-Path $XAMPPPath 'xampp_start.bat'

    if (Test-Path $xamppStart) {
        Write-Host "Found xampp_start.exe — launching it in a new window..."
        Start-Process -FilePath $xamppStart
        Start-Sleep -Seconds 3
        return
    } elseif (Test-Path $xamppBat) {
        Write-Host "Found xampp_start.bat — running it..."
        Start-Process -FilePath $xamppBat -WorkingDirectory $XAMPPPath
        Start-Sleep -Seconds 3
        return
    }

    # Try common Windows service names
    $servicesToTry = @('Apache2.4','apache2.4','mysql','MySQL','mysqld','mariadb')
    foreach ($s in $servicesToTry) {
        try {
            $svc = Get-Service -Name $s -ErrorAction Stop
            if ($svc.Status -ne 'Running') {
                Write-Host "Starting service $s..."
                Start-Service -Name $s -ErrorAction Stop
                Start-Sleep -Seconds 2
            } else {
                Write-Host "Service $s already running."
            }
        } catch {
            # ignore missing services
        }
    }

    Write-Host "If Apache or MySQL did not start, please open the XAMPP Control Panel manually."
}

function Import-DatabaseSchema {
    $mysqlExe = Join-Path $XAMPPPath 'mysql\\bin\\mysql.exe'
    $sqlFile = Join-Path $ProjectPath 'sql\\etudesync_schema.sql'

    if (-not (Test-Path $sqlFile)) {
        Write-Host "SQL schema not found at $sqlFile. Skipping import." -ForegroundColor Yellow
        return
    }
    if (-not (Test-Path $mysqlExe)) {
        Write-Host "mysql.exe not found at $mysqlExe. Install XAMPP or adjust -XAMPPPath." -ForegroundColor Red
        return
    }

    if ([string]::IsNullOrEmpty($DBPass)) {
        $DBPass = Read-Host -Prompt "Enter MySQL password for user '$DBUser' (leave blank for empty)"
    }

    # Build command. Use cmd.exe to allow input redirection (<)
    if ([string]::IsNullOrEmpty($DBPass)) {
        $cmd = "`"$mysqlExe`" -u $DBUser -p\"\" $DBName < `"$sqlFile`""
    } else {
        # Avoid exposing password in process list if you prefer to be prompted — user chose to pass it
        $cmd = "`"$mysqlExe`" -u $DBUser -p$DBPass $DBName < `"$sqlFile`""
    }

    Write-Host "Importing schema into database '$DBName' (this may take a few seconds)..."
    $proc = Start-Process -FilePath cmd.exe -ArgumentList "/c $cmd" -NoNewWindow -Wait -PassThru
    if ($proc.ExitCode -eq 0) {
        Write-Host "Database import finished successfully." -ForegroundColor Green
    } else {
        Write-Host "Database import finished with exit code $($proc.ExitCode). Check mysql logs or run the command manually." -ForegroundColor Yellow
    }
}

function Ensure-UploadsDir {
    $uploads = Join-Path $ProjectPath 'assets\\uploads\\room_files'
    if (-not (Test-Path $uploads)) {
        Write-Host "Creating uploads directory: $uploads"
        New-Item -ItemType Directory -Path $uploads -Force | Out-Null
    } else {
        Write-Host "Uploads directory already exists: $uploads"
    }

    try {
        Write-Host "Granting Modify permissions to current user on $uploads"
        icacls $uploads /grant "$env:USERNAME:(OI)(CI)M" /T | Out-Null
    } catch {
        Write-Host "Failed to update permissions. You may need to run PowerShell as Administrator." -ForegroundColor Yellow
    }
}

function Open-AppInBrowser {
    $url = 'http://localhost/etudesync/public/'
    Write-Host "Opening $url in default browser..."
    Start-Process $url
}

# Execution starts here
Start-XamppIfPossible
Ensure-UploadsDir
Import-DatabaseSchema
Open-AppInBrowser

Write-Host "Done. If the app doesn't appear, check Apache/MySQL status in the XAMPP Control Panel and inspect logs." -ForegroundColor Cyan
