<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;background:#f4eee2;font-family:Arial,Helvetica,sans-serif;color:#20211c">
    <div style="max-width:560px;margin:0 auto;padding:24px">
        <div style="background:#16233c;color:#fff;padding:20px 24px;border-radius:12px 12px 0 0">
            <strong style="font-size:18px;letter-spacing:-0.5px">ShipFast</strong>
        </div>
        <div style="background:#fff;padding:24px;border-radius:0 0 12px 12px">
            <h1 style="font-size:20px;margin:0 0 12px">{{ $subjectLine }}</h1>
            <p style="font-size:15px;line-height:1.6;margin:0 0 16px">{{ $bodyLine }}</p>
            <table style="width:100%;font-size:14px;border-collapse:collapse">
                <tr><td style="padding:6px 0;color:#777">Tracking number</td><td style="padding:6px 0;text-align:right;font-family:monospace">{{ $shipment->tracking_number }}</td></tr>
                <tr><td style="padding:6px 0;color:#777">Route</td><td style="padding:6px 0;text-align:right">{{ $shipment->origin_city }} &rarr; {{ $shipment->destination_city }}</td></tr>
                <tr><td style="padding:6px 0;color:#777">Status</td><td style="padding:6px 0;text-align:right;text-transform:capitalize">{{ str_replace('_',' ',$shipment->status) }}</td></tr>
            </table>
        </div>
        <p style="text-align:center;color:#999;font-size:12px;margin-top:16px">&copy; {{ date('Y') }} ShipFast</p>
    </div>
</body>
</html>
