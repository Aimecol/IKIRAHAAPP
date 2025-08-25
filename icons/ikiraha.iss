; =========================================
; IKIRAHA Windows Installer - Inno Setup
; =========================================

#define MyAppName "IKIRAHA"
#define MyAppVersion "1.0.0"
#define MyAppPublisher "IKIGUGU Group Ltd"
#define MyAppURL "https://ikigugugroup.rw"
#define MyAppExeName "IKIRAHA.exe"  ; Your Flutter Windows release EXE
#define MyAppIcon "D:\Projects\Software Engineering\Mobile Applications\Andoid\ikirahaapp\icons\myapp.ico" ; Installer & shortcut icon
#define LicenseFile "D:\Projects\Software Engineering\Mobile Applications\Andoid\ikirahaapp\license.txt"
#define InfoBeforeFile "D:\Projects\Software Engineering\Mobile Applications\Andoid\ikirahaapp\readme_before.txt"
#define InfoAfterFile "D:\Projects\Software Engineering\Mobile Applications\Andoid\ikirahaapp\readme_after.txt"
#define FlutterReleaseDir "D:\Projects\Software Engineering\Mobile Applications\Andoid\ikirahaapp\build\windows\x64\runner\Release" ; Path to Flutter release

[Setup]
AppId={{17F46151-587A-446C-BE90-38B0FA3BE06C}}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppPublisher={#MyAppPublisher}
AppPublisherURL={#MyAppURL}
AppSupportURL={#MyAppURL}
AppUpdatesURL={#MyAppURL}
DefaultDirName={pf}\{#MyAppName}
UninstallDisplayIcon={app}\{#MyAppExeName}
ArchitecturesAllowed=x64
ArchitecturesInstallIn64BitMode=x64
ChangesAssociations=yes
LicenseFile={#LicenseFile}
InfoBeforeFile={#InfoBeforeFile}
InfoAfterFile={#InfoAfterFile}
OutputDir=D:\Projects\Software Engineering\Mobile Applications\Andoid\ikirahaapp\build\windows\Installer
OutputBaseFilename={#MyAppName}_Setup_v{#MyAppVersion}
SetupIconFile={#MyAppIcon}
SolidCompression=yes
WizardStyle=modern

[Files]
; Copy Flutter app files
Source: "{#FlutterReleaseDir}\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs
; Copy installer icon (optional)
Source: "{#MyAppIcon}"; DestDir: "{app}"; Flags: ignoreversion

[Icons]
; Start Menu shortcut
Name: "{autoprograms}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; IconFilename: "{app}\myapp.ico"
; Desktop shortcut
Name: "{autodesktop}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; IconFilename: "{app}\myapp.ico"; Tasks: desktopicon

[Tasks]
Name: "desktopicon"; Description: "Create a &desktop shortcut"; Flags: unchecked

[Registry]
; Uninstall information
Root: HKLM; Subkey: "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\IKIRAHA"; ValueType: string; ValueName: "DisplayName"; ValueData: "{#MyAppName}"; Flags: uninsdeletevalue
Root: HKLM; Subkey: "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\IKIRAHA"; ValueType: string; ValueName: "Publisher"; ValueData: "{#MyAppPublisher}"; Flags: uninsdeletevalue
Root: HKLM; Subkey: "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\IKIRAHA"; ValueType: string; ValueName: "DisplayVersion"; ValueData: "{#MyAppVersion}"; Flags: uninsdeletevalue
Root: HKLM; Subkey: "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\IKIRAHA"; ValueType: string; ValueName: "InstallLocation"; ValueData: "{app}"; Flags: uninsdeletevalue
Root: HKLM; Subkey: "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\IKIRAHA"; ValueType: string; ValueName: "DisplayIcon"; ValueData: "{app}\{#MyAppExeName}"; Flags: uninsdeletevalue
Root: HKLM; Subkey: "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\IKIRAHA"; ValueType: string; ValueName: "UninstallString"; ValueData: "{uninstallexe}"; Flags: uninsdeletevalue
Root: HKLM; Subkey: "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\IKIRAHA"; ValueType: string; ValueName: "URLInfoAbout"; ValueData: "{#MyAppURL}"; Flags: uninsdeletevalue
Root: HKLM; Subkey: "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\IKIRAHA"; ValueType: string; ValueName: "HelpLink"; ValueData: "mailto:support@ikigugu.com"; Flags: uninsdeletevalue

; Optional file association
Root: HKCR; Subkey: ".myp"; ValueType: string; ValueName: ""; ValueData: "{#MyAppName} File"; Flags: uninsdeletekey
Root: HKCR; Subkey: "{#MyAppName}File"; ValueType: string; ValueName: ""; ValueData: "{#MyAppName} File"; Flags: uninsdeletekey
Root: HKCR; Subkey: "{#MyAppName}File\DefaultIcon"; ValueType: string; ValueName: ""; ValueData: "{app}\{#MyAppExeName},0"
Root: HKCR; Subkey: "{#MyAppName}File\shell\open\command"; ValueType: string; ValueName: ""; ValueData: """{app}\{#MyAppExeName}"" ""%1"""

[Run]
; Launch app after installation
Filename: "{app}\{#MyAppExeName}"; Description: "Launch {#MyAppName}"; Flags: nowait postinstall skipifsilent
