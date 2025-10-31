<!DOCTYPE html>
<html>

<head>
    <title>RedCode Intern Blog</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <posts-component :posts="{{ json_encode($posts) }}"></posts-component>
    </div>
</body>

</html>