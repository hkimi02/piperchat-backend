<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation à rejoindre {{ $organisation_name }}</title>
    <style>
        body, table, td, a {
            text-size-adjust: 100%;
            font-family: Arial, sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100vh;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f4f4f4;
            border-radius: 8px;
            border: 1px solid #dddddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background-color: #307B34;
            padding: 12px;
            color: #F9F0E3;
            text-align: left;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .email-header img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-block;
            vertical-align: middle;
        }
        .email-header-text {
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }
        .email-subject {
            font-weight: bold;
            font-size: 16px;
        }
        .email-title {
            font-size: 14px;
            margin-top: 4px;
        }
        .email-body p {
            color: #333333;
        }
        .greeting, .email-body {
            padding: 20px 20px 0 20px;
            color: #333333;
            line-height: 1.4;
        }
        .salutation {
            padding: 10px 20px 0 20px;
            color: #333333;
            line-height: 1.4;
        }
        .email-footer {
            text-align: center;
            font-size: 12px;
            color: #999999;
            padding: 10px 20px;
        }
        .code {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            background-color: #7dd97e;
            color: #000000;
            border-radius: 5px;
            display: inline-block;
            line-height: normal;
            letter-spacing: .3rem;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #307B34;
            color: #F9F0E3;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<table class="email-container" role="presentation" width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td class="email-header">
            <img src="{{ asset('images/email-logo.png') }}" alt="Logo">
            <div class="email-header-text">
                <div class="email-subject">Invitation à rejoindre {{ $organisation_name }}</div>
                <div class="email-title">Bienvenue sur {{ $app_name }}!</div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="greeting">
            Bonjour,
        </td>
    </tr>
    <tr>
        <td class="email-body">
            <p>Vous avez été invité à rejoindre l'organisation <strong>{{ $organisation_name }}</strong> sur <strong>{{ $app_name }}</strong>.</p>
            <p>Veuillez utiliser le code suivant pour rejoindre l'organisation :</p>
            <div class="code">{{ $join_code }}</div>
            <p>Ou cliquez sur le bouton ci-dessous pour rejoindre directement :</p>
            <a href="{{ $join_url }}" class="button">Rejoindre l'organisation</a>
        </td>
    </tr>
    <tr>
        <td class="salutation">
            Meilleures salutations, <br>
            L'équipe {{ $app_name }}!
        </td>
    </tr>
    <tr>
        <td class="email-footer">
            © {{ now()->year }} {{ $app_name }}. Tous droits réservés.
        </td>
    </tr>
</table>
</body>
</html>
