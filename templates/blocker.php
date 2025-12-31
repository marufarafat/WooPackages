<?php

declare(strict_types=1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>License Required</title>
    <style>
        :root{
            --bg-a:#f6f1ea;
            --bg-b:#f3e6d6;
            --ink:#1f1b16;
            --accent:#c45a3a;
            --muted:#6a5a4a;
            --panel:#fff7ec;
            --shadow:0 20px 60px rgba(40,24,8,0.18);
        }
        *{box-sizing:border-box;}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:radial-gradient(1200px 600px at 10% 10%,#fff,transparent 60%),
            linear-gradient(140deg,var(--bg-a),var(--bg-b));
            color:var(--ink);font-family:"Palatino Linotype","Book Antiqua",Palatino,serif;}
        .wrap{position:relative;padding:48px 24px;width:min(900px,92vw);}
        .card{position:relative;background:var(--panel);border:1px solid rgba(120,80,40,0.2);
            border-radius:24px;padding:40px 36px 32px;box-shadow:var(--shadow);overflow:hidden;}
        .card:before{content:"";position:absolute;inset:-60px -80px auto auto;width:200px;height:200px;
            background:radial-gradient(circle,rgba(196,90,58,0.25),transparent 70%);
            transform:rotate(20deg);}
        .badge{display:inline-flex;align-items:center;gap:10px;
            padding:8px 14px;border-radius:999px;background:#f0ddcc;color:#6d3f2e;
            font-family:"Trebuchet MS","Lucida Sans Unicode","Lucida Grande",sans-serif;
            font-size:12px;letter-spacing:0.16em;text-transform:uppercase;}
        h1{margin:18px 0 10px;font-size:42px;line-height:1.1;
            font-family:"Trebuchet MS","Lucida Sans Unicode","Lucida Grande",sans-serif;}
        p{margin:0 0 18px;color:var(--muted);font-size:17px;}
        .message{margin-top:18px;padding:18px 20px;border-left:4px solid var(--accent);
            background:rgba(196,90,58,0.08);border-radius:12px;
            font-size:16px;line-height:1.5;white-space:pre-wrap;}
        .footer{margin-top:24px;font-size:13px;color:var(--muted);}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px;margin-top:24px;}
        .chip{padding:14px 16px;border-radius:14px;background:#f7e9da;border:1px solid rgba(120,80,40,0.2);
            font-size:14px;color:#5b4636;}
        @keyframes rise{from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);}}
        .card{animation:rise 500ms ease-out;}
        .badge,.message,.chip{animation:rise 600ms ease-out;}
        @media (max-width:640px){.card{padding:28px 22px;}h1{font-size:32px;}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <span class="badge">License Check</span>
        <h1>Access blocked</h1>
        <p>Your application is currently blocked because the license could not be verified.</p>
        <div class="message"><?= $message ?></div>
        <div class="grid">
            <div class="chip">Confirm your license key is correct.</div>
            <div class="chip">Verify the domain matches your activation.</div>
            <div class="chip">Contact support if this persists.</div>
        </div>
        <div class="footer">This message is controlled by the license server.</div>
    </div>
</div>
</body>
</html>
