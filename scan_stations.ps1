# Scanner IP Jaringan Lokal untuk Deteksi Hardware Station (ESP8266 / ESP32)
$ErrorActionPreference = "SilentlyContinue"

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host " [?] PEMBDAHUB - PENCARI IP HARDWARE STATION (ESP8266/ESP32)" -ForegroundColor Cyan
Write-Host "========================================================" -ForegroundColor Cyan

# 1. Deteksi Interface Wi-Fi / IP Lokal
$localIP = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { 
    $_.InterfaceAlias -match "Wi-Fi" -or ($_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.254.*" -and $_.IPAddress -notlike "100.*") 
}).IPAddress | Select-Object -First 1

if (-not $localIP) {
    $localIP = "192.168.1.10"
}

$parts = $localIP.Split(".")
$subnet = $parts[0] + "." + $parts[1] + "." + $parts[2]

Write-Host "[+] IP Komputer Anda : $localIP" -ForegroundColor White
Write-Host "[+] Subnet Target    : $subnet" -NoNewline -ForegroundColor White
Write-Host ".0/24" -ForegroundColor White
Write-Host "[+] Sedang memindai 254 IP di jaringan..." -ForegroundColor Yellow

# 2. Ping sweep multi-thread cepat via .NET
$scriptBlock = {
    param($targetIp)
    $p = New-Object System.Net.NetworkInformation.Ping
    try {
        $r = $p.Send($targetIp, 250)
        if ($r.Status -eq "Success") { return $targetIp }
    } catch {}
    return $null
}

$sessionState = [System.Management.Automation.Runspaces.InitialSessionState]::CreateDefault()
$pool = [System.Management.Automation.Runspaces.RunspaceFactory]::CreateRunspacePool(1, 50, $sessionState, $Host)
$pool.Open()

$runspaces = @()
1..254 | ForEach-Object {
    $ip = "$subnet.$_"
    $ps = [System.Management.Automation.PowerShell]::Create().AddScript($scriptBlock).AddArgument($ip)
    $ps.RunspacePool = $pool
    $runspaces += [PSCustomObject]@{
        Pipe = $ps
        AsyncResult = $ps.BeginInvoke()
        IP = $ip
    }
}

foreach ($r in $runspaces) {
    $null = $r.Pipe.EndInvoke($r.AsyncResult)
    $r.Pipe.Dispose()
}
$pool.Close()
$pool.Dispose()

# 3. Baca Tabel ARP
$arpLines = arp -a
$foundDevices = @()

# Database OUI MAC Vendor Espressif (NodeMCU ESP-12F & ESP32)
$espressifOUIs = @(
    "10-52-1c", "18-fe-34", "24-0a-c4", "24-6f-28", "24-b2-de", "2c-3a-e8", "30-ae-a4", "3c-61-05", 
    "3c-71-bf", "40-22-d8", "40-91-51", "48-3f-da", "48-55-19", "4c-11-ae", "50-02-91", "54-43-b2", 
    "5c-cf-7f", "60-01-94", "60-55-f9", "68-c6-3a", "70-03-9f", "70-b3-d5", "7c-87-ce", "7c-df-a1", 
    "80-7d-3a", "84-0d-8e", "84-cc-a8", "84-f3-eb", "90-97-d5", "94-b5-55", "94-b9-7e", "a0-20-a6", 
    "a4-cf-12", "a4-e5-7c", "ac-0b-fb", "ac-67-b2", "ac-d0-74", "b4-e6-2d", "bc-dd-c2", "c4-4f-33", 
    "c4-dd-57", "cc-50-e3", "d8-a0-1d", "d8-bf-c0", "dc-4f-22", "dc-54-75", "e0-98-06", "e8-31-cd", 
    "e8-db-84", "ec-62-60", "ec-fa-bc"
)

foreach ($line in $arpLines) {
    $clean = $line.Trim()
    if ($clean -match "^(\d+\.\d+\.\d+\.\d+)\s+([0-9a-fA-F-]+)\s+(\w+)") {
        $ip = $matches[1]
        $mac = $matches[2].ToLower().Replace(":", "-")
        $type = $matches[3]
        
        if (-not ($ip.StartsWith($subnet + "."))) { continue }
        if ($mac -eq "ff-ff-ff-ff-ff-ff" -or $ip.EndsWith(".255")) { continue }
        
        $macPrefix = if ($mac.Length -ge 8) { $mac.Substring(0, 8) } else { "" }
        $isEsp = $espressifOUIs -contains $macPrefix
        
        $foundDevices += [PSCustomObject]@{
            IP = $ip
            MAC = $mac
            Type = $type
            IsEspressif = $isEsp
        }
    }
}

Write-Host ""
Write-Host "--------------------------------------------------------" -ForegroundColor DarkGray
Write-Host " [i] HASIL PEMINDAIAN JARINGAN ($($foundDevices.Count) Perangkat Aktif)" -ForegroundColor Cyan
Write-Host "--------------------------------------------------------" -ForegroundColor DarkGray

$espCount = 0
foreach ($dev in $foundDevices) {
    if ($dev.IsEspressif) {
        $espCount++
        Write-Host " [* ESPRESSIF STATION] " -NoNewline -ForegroundColor Green
        Write-Host "IP: $($dev.IP.PadRight(15)) " -NoNewline -ForegroundColor Yellow
        Write-Host "| MAC: $($dev.MAC) " -NoNewline -ForegroundColor White
        Write-Host "-> TERDETEKSI SEBAGAI STATION NODEMCU / ESP32" -ForegroundColor Green
    } else {
        Write-Host " [  Perangkat Lain   ] " -NoNewline -ForegroundColor Gray
        Write-Host "IP: $($dev.IP.PadRight(15)) " -NoNewline -ForegroundColor Gray
        Write-Host "| MAC: $($dev.MAC)" -ForegroundColor Gray
    }
}

Write-Host "--------------------------------------------------------" -ForegroundColor DarkGray
if ($espCount -gt 0) {
    Write-Host "[OK] DITEMUKAN $espCount HARDWARE STATION ESP8266/ESP32 AKTIF DI JARINGAN INI!" -ForegroundColor Green
} else {
    Write-Host "[!] Tidak ada chip Espressif (NodeMCU/ESP32) di subnet $subnet.0/24." -ForegroundColor Yellow
    Write-Host "    Jika station terhubung ke router lain (misal PembdaLINK / Hotspot lain)," -ForegroundColor Gray
    Write-Host "    pastikan komputer Anda terhubung ke Wi-Fi yang sama dengan station." -ForegroundColor Gray
}
Write-Host "========================================================" -ForegroundColor Cyan
