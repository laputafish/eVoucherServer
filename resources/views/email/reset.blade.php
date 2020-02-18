<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>

<div>
    Hi {{ $name }},
    <p>
    Please click on the link below or copy it into the address bar of your browser to reset your password:
    </p>
    <a href="{{ $link }}">[Confirm my email address]</a>
    <br/>
</div>

</body>
</html>