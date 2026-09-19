<script src="{{ asset('js/pergabi-qr.js') }}"></script>
<script>
    window.whenPergabiQrReady = function (callback) {
        if (window.PergabiQr) {
            callback();
            return;
        }

        var tries = 0;
        var timer = setInterval(function () {
            tries += 1;
            if (window.PergabiQr || tries > 40) {
                clearInterval(timer);
                if (window.PergabiQr) {
                    callback();
                }
            }
        }, 50);
    };

    window.drawPergabiQr = function (canvas, payload, size) {
        if (! canvas || ! payload || ! window.PergabiQr) {
            return Promise.resolve();
        }

        window.PergabiQr.toCanvas(canvas, payload, size || canvas.width || 160);
        return Promise.resolve();
    };

    window.drawPergabiQrAll = function (selector, payload, size) {
        return new Promise(function (resolve) {
            window.whenPergabiQrReady(function () {
                document.querySelectorAll(selector).forEach(function (canvas) {
                    window.drawPergabiQr(canvas, payload, size);
                });
                resolve();
            });
        });
    };
</script>
