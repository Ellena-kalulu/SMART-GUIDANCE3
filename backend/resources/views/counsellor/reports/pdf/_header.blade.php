<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000000; background: #ffffff; }
  .page-header { background: #2563eb; color: #ffffff; padding: 18px 24px; margin-bottom: 20px; border-radius: 6px; }
  .page-header .report-num { font-size: 9px; letter-spacing: 1px; text-transform: uppercase; color: #dbeafe; margin-bottom: 4px; }
  .page-header h1 { font-size: 18px; font-weight: 700; margin-bottom: 2px; }
  .page-header .subtitle { font-size: 10px; color: #dbeafe; }
  .meta { display: flex; justify-content: space-between; font-size: 9px; color: #000000; margin-bottom: 16px; }
  h2 { font-size: 12px; font-weight: 700; color: #000000; margin: 16px 0 8px; border-bottom: 1px solid #000000; padding-bottom: 4px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  th { background: #000000; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; color: #000000; padding: 6px 8px; text-align: left; border-bottom: 1px solid #000000; }
  td { padding: 6px 8px; border-bottom: 1px solid #000000; font-size: 10px; color: #000000; vertical-align: top; }
  tr:nth-child(even) td { background: #000000; }
  .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: 600; }
  .badge-red { background: #dbeafe; color: #1d4ed8; }
  .badge-green { background: #dbeafe; color: #1d4ed8; }
  .badge-orange { background: #dbeafe; color: #000000; }
  .badge-blue { background: #dbeafe; color: #1d4ed8; }
  .badge-purple { background: #dbeafe; color: #1d4ed8; }
  .stat-row { display: flex; gap: 12px; margin-bottom: 16px; }
  .stat-box { flex: 1; background: #000000; border: 1px solid #000000; border-radius: 4px; padding: 10px; text-align: center; }
  .stat-box .val { font-size: 20px; font-weight: 700; color: #2563eb; }
  .stat-box .lbl { font-size: 9px; color: #000000; margin-top: 2px; }
  .bar-wrap { background: #000000; border-radius: 3px; height: 8px; margin: 3px 0 8px; }
  .bar-fill { height: 8px; border-radius: 3px; background: #2563eb; }
  .footer { margin-top: 24px; padding-top: 8px; border-top: 1px solid #000000; font-size: 9px; color: #000000; display: flex; justify-content: space-between; }
</style>
</head>
<body>
