<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Document</title>
</head>
<body>

<video autoplay></video>

<script>
    var onFailSoHard = function(e) {
        console.log('Reeeejected!', e);
    };

    var video = document.querySelector('video');

    if (navigator.getUserMedia) {
        navigator.getUserMedia({audio: true, video: true}, function(stream) {
            video.src = stream;
        }, onFailSoHard);
    } else if (navigator.webkitGetUserMedia) {
        navigator.webkitGetUserMedia('audio, video', function(stream) {
            video.src = window.webkitURL.createObjectURL(stream);
        }, onFailSoHard);
    } else {
        video.src = 'somevideo.webm'; // fallback.
    }
</script>
</body>
</html>