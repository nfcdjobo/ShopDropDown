<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#C2410C">
    <title>Hors connexion</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;
             padding:1.5rem;background:#FBF8F3;color:#1c1917;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;text-align:center}
        main{max-width:26rem}
        .ico{width:4.5rem;height:4.5rem;margin:0 auto 1.5rem;border-radius:50%;background:#C2410C;color:#fff;
             display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700}
        h1{font-size:1.6rem;margin:0 0 .75rem}
        p{margin:0 0 1.5rem;color:#57534e;line-height:1.55}
        button{border:0;border-radius:999px;padding:.85rem 1.75rem;background:#1c1917;color:#fff;font-size:1rem;font-weight:600;cursor:pointer}
    </style>
</head>
<body>
    <main>
        <div class="ico" aria-hidden="true">S</div>
        <h1>Vous êtes hors connexion</h1>
        <p>Impossible de joindre la boutique pour le moment. Vérifiez votre réseau puis réessayez.</p>
        <button type="button" onclick="location.reload()">Réessayer</button>
    </main>
</body>
</html>
