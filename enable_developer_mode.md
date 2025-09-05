# Enable Windows Developer Mode for Flutter

## 🔧 Method 1: Using Settings UI (Recommended)

1. **Open Windows Settings**:
   - Press `Windows + I` keys
   - Or run this command in Command Prompt: `start ms-settings:developers`

2. **Navigate to Developer Settings**:
   - Go to **Privacy & Security** → **For developers**
   - Or search for "Developer settings" in the search bar

3. **Enable Developer Mode**:
   - Toggle **Developer Mode** to **On**
   - Click **Yes** when prompted with security warning
   - Wait for the installation to complete

4. **Restart** your computer (recommended)

## 🔧 Method 2: Using PowerShell (Advanced)

Run PowerShell as Administrator and execute:

```powershell
# Enable Developer Mode
reg add "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows\CurrentVersion\AppModelUnlock" /t REG_DWORD /f /v "AllowDevelopmentWithoutDevLicense" /d "1"

# Enable sideloading
reg add "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Windows\CurrentVersion\AppModelUnlock" /t REG_DWORD /f /v "AllowAllTrustedApps" /d "1"

# Restart required
Write-Host "Developer Mode enabled. Please restart your computer."
```

## 🔧 Method 3: Using Group Policy (Enterprise)

1. Open **Group Policy Editor** (`gpedit.msc`)
2. Navigate to: **Computer Configuration** → **Administrative Templates** → **Windows Components** → **App Package Deployment**
3. Enable: **Allow development of Windows Store apps and installing them from an integrated development environment (IDE)**

## ✅ Verify Developer Mode is Enabled

After enabling and restarting:

1. Open **Settings** → **Privacy & Security** → **For developers**
2. Verify **Developer Mode** shows as **On**
3. You should see additional developer options like:
   - Device Portal
   - Device discovery
   - File Explorer integration

## 🚀 Test Flutter Windows App

Once Developer Mode is enabled:

```bash
# Navigate to your project
cd C:\xampp\htdocs\ikirahaapp

# Run on Windows
flutter run -d windows

# Or build for Windows
flutter build windows
```

## 🔍 Troubleshooting

### If Developer Mode won't enable:
1. **Check Windows Version**: Requires Windows 10 version 1607 or later
2. **Run as Administrator**: Some systems require admin privileges
3. **Check Group Policy**: Enterprise systems may have restrictions
4. **Windows Update**: Ensure Windows is up to date

### If Flutter still shows symlink error:
1. **Restart Command Prompt/PowerShell** after enabling Developer Mode
2. **Restart your computer** completely
3. **Run Flutter Doctor**: `flutter doctor -v` to check for issues

### Alternative: Use Web or Mobile Emulator
If Windows Developer Mode can't be enabled:
- **Web**: `flutter run -d chrome` (we've fixed the web issues)
- **Android Emulator**: Install Android Studio and create an emulator
- **iOS Simulator**: Available on macOS only

## 📱 Alternative Testing Methods

### 1. Web Browser (Chrome/Edge)
```bash
flutter run -d chrome --web-renderer html
```

### 2. Android Emulator
```bash
# Install Android Studio first
# Create an Android Virtual Device (AVD)
flutter run -d android
```

### 3. Physical Android Device
```bash
# Enable USB Debugging on your Android device
# Connect via USB
flutter run -d android
```

## 🔒 Security Considerations

**Developer Mode enables**:
- Installation of unsigned apps
- PowerShell script execution
- Access to developer tools
- Sideloading capabilities

**Recommendations**:
- Only enable on development machines
- Disable when not actively developing
- Keep Windows Defender enabled
- Be cautious with unknown software

## 📞 Need Help?

If you continue to have issues:
1. Check Windows version: `winver`
2. Run Flutter doctor: `flutter doctor -v`
3. Check system requirements
4. Consider using web version for testing

---

**Next Steps**: After enabling Developer Mode, restart your computer and try running the Flutter app again with `flutter run -d windows`.
