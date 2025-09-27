$ErrorActionPreference = 'Stop'

function Invoke-Json {
      param(
            [string]$Method,
            [string]$Uri,
            [hashtable]$Headers,
            [object]$Body
      )
      if ($null -ne $Body) {
            $json = $Body | ConvertTo-Json -Depth 6
            return Invoke-RestMethod -Method $Method -Uri $Uri -Headers $Headers -Body $json -ContentType 'application/json'
      }
      else {
            return Invoke-RestMethod -Method $Method -Uri $Uri -Headers $Headers
      }
}

$base = 'http://127.0.0.1:8000'

$summary = [ordered]@{}

# Login (seeded admin)
$login = Invoke-Json -Method 'POST' -Uri ($base + '/api/login') -Body @{ email = 'admin@humanitarian.aid'; password = 'password' }
$token = $login.token
if (-not $token) { throw 'Login returned no token' }
$summary.login_user = $login.user.email

$headers = @{ Authorization = 'Bearer ' + $token }

# /api/user
$me = Invoke-Json -Method 'GET' -Uri ($base + '/api/user') -Headers $headers
$summary.user_id = $me.id
$summary.user_role = $me.role

# Users helpers
$volunteers = Invoke-Json -Method 'GET' -Uri ($base + '/api/users/volunteers') -Headers $headers
$beneficiaries = Invoke-Json -Method 'GET' -Uri ($base + '/api/users/beneficiaries') -Headers $headers
$summary.volunteers_count = @($volunteers).Count
$summary.beneficiaries_count = @($beneficiaries).Count

# Donation lifecycle
$don = Invoke-Json -Method 'POST' -Uri ($base + '/api/donations') -Headers $headers -Body @{ donor_name = 'QA Donor'; type = 'food'; quantity = 5 }
$donApproved = Invoke-Json -Method 'POST' -Uri ($base + '/api/donations/' + $don.id + '/approve') -Headers $headers
$summary.donation = @{ id = $donApproved.id; status = $donApproved.status }

# Distribution create (requires approved donation and valid user ids)
$volId = $volunteers[0].id
$benId = $beneficiaries[0].id
$dist = Invoke-Json -Method 'POST' -Uri ($base + '/api/distributions') -Headers $headers -Body @{ volunteer_id = $volId; beneficiary_id = $benId; donation_id = $donApproved.id }
$summary.distribution = @{ id = $dist.id; delivery_status = $dist.delivery_status }

# Aid requests index (auth protected)
$aidRequests = Invoke-Json -Method 'GET' -Uri ($base + '/api/aid-requests') -Headers $headers
$summary.aid_requests_count = @($aidRequests).Count

# Notifications & dashboard
$notifs = Invoke-Json -Method 'GET' -Uri ($base + '/api/notifications') -Headers $headers
$unread = Invoke-Json -Method 'GET' -Uri ($base + '/api/notifications/unread-count') -Headers $headers
Invoke-Json -Method 'POST' -Uri ($base + '/api/notifications/mark-all-read') -Headers $headers | Out-Null
$unreadAfter = Invoke-Json -Method 'GET' -Uri ($base + '/api/notifications/unread-count') -Headers $headers
$summary.notifications_before = $unread.count
$summary.notifications_after = $unreadAfter.count

$stats = Invoke-Json -Method 'GET' -Uri ($base + '/api/dashboard/stats') -Headers $headers
$summary.dashboard_stats = $stats

# Upload document (simple small text upload)
try {
      $tmp = New-TemporaryFile
      Set-Content -Path $tmp -Value 'test upload'
      $form = @{
            file = Get-Item $tmp
            type = 'document'
      }
      $upload = Invoke-RestMethod -Method Post -Uri ($base + '/api/upload-document') -Headers $headers -Form $form
      $summary.upload_ok = [bool]$upload.url
}
catch {
      $summary.upload_error = $_.Exception.Message
}

$summary | ConvertTo-Json -Depth 6


