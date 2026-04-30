# start-octane.ps1
# PowerShell script to start Laravel Octane with RoadRunner and Queue Worker on Windows

# Set the working directory to the project root
Set-Location -Path "E:\Github\japekv3\backend"

# Set environment variables for production
$env:APP_ENV="production"
$env:APP_DEBUG="false"

Write-Host "Starting Laravel Queue Worker in background..."
# Start queue worker in a separate background process
$queueJob = Start-Job -Name "LaravelQueueWorker" -ScriptBlock {
    Set-Location -Path "E:\Github\japekv3\backend"
    php artisan queue:work --sleep=3 --tries=3 --max-jobs=1000 --timeout=300
}
Write-Host "Queue Worker started with Job ID: $($queueJob.Id)"

Write-Host ""
Write-Host "Starting Laravel Octane with RoadRunner..."
Write-Host "Access your application at http://0.0.0.0:9000"
Write-Host "Press CTRL+C to stop the server"
Write-Host ""

# Use a single worker and disable signal handling to avoid Windows issues
try {
    php artisan octane:start --host=0.0.0.0 --port=9000 --workers=1 --max-requests=500 --server=roadrunner
} catch {
    Write-Host "Octane server stopped or encountered an error:"
    Write-Host $_.Exception.Message
} finally {
    # Stop the queue worker when Octane stops
    Write-Host "Stopping Queue Worker..."
    Stop-Job -Name "LaravelQueueWorker" -ErrorAction SilentlyContinue
    Remove-Job -Name "LaravelQueueWorker" -ErrorAction SilentlyContinue
    Write-Host "Queue Worker stopped."
}
