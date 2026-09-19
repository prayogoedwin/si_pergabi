(function (root) {
    var EXP = new Array(512);
    var LOG = new Array(256);
    (function initGf() {
        var x = 1;
        for (var i = 0; i < 255; i++) {
            EXP[i] = x;
            LOG[x] = i;
            x <<= 1;
            if (x & 256) {
                x ^= 0x11d;
            }
        }
        for (i = 255; i < 512; i++) {
            EXP[i] = EXP[i - 255];
        }
    }());

    function gfMul(a, b) {
        if (a === 0 || b === 0) {
            return 0;
        }
        return EXP[LOG[a] + LOG[b]];
    }

    function rsGenerator(count) {
        var poly = [1];
        for (var i = 0; i < count; i++) {
            var next = new Array(poly.length + 1).fill(0);
            for (var j = 0; j < poly.length; j++) {
                next[j] ^= poly[j];
                next[j + 1] ^= gfMul(poly[j], EXP[i]);
            }
            poly = next;
        }
        return poly;
    }

    function rsEncode(data, ecCount) {
        var gen = rsGenerator(ecCount);
        var ecc = new Array(ecCount).fill(0);
        for (var i = 0; i < data.length; i++) {
            var factor = data[i] ^ ecc[0];
            ecc.shift();
            ecc.push(0);
            if (factor === 0) {
                continue;
            }
            for (var j = 0; j < gen.length - 1; j++) {
                ecc[j] ^= gfMul(gen[j + 1], factor);
            }
        }
        return ecc;
    }

    var BLOCKS = {
        1: [10, 1, 16, 0, 0],
        2: [16, 1, 28, 0, 0],
        3: [26, 1, 44, 0, 0],
        4: [18, 2, 32, 0, 0],
        5: [24, 2, 43, 0, 0],
        6: [16, 4, 27, 0, 0],
        7: [18, 4, 31, 0, 0],
        8: [22, 2, 38, 2, 39],
        9: [22, 3, 36, 2, 37],
        10: [26, 4, 43, 1, 44],
        11: [30, 1, 50, 4, 51],
        12: [22, 6, 36, 2, 37],
        13: [22, 8, 37, 1, 38],
        14: [24, 4, 40, 5, 41],
        15: [24, 5, 41, 5, 42],
        16: [28, 7, 45, 3, 46],
        17: [28, 10, 46, 1, 47],
        18: [26, 9, 43, 4, 44],
        19: [26, 3, 44, 11, 45],
        20: [26, 3, 41, 13, 42],
    };

    var ALIGN = {
        2: [6, 18],
        3: [6, 22],
        4: [6, 26],
        5: [6, 30],
        6: [6, 34],
        7: [6, 22, 38],
        8: [6, 24, 42],
        9: [6, 26, 46],
        10: [6, 28, 50],
        11: [6, 30, 54],
        12: [6, 32, 58],
        13: [6, 34, 62],
        14: [6, 26, 46, 66],
        15: [6, 26, 48, 70],
        16: [6, 26, 50, 74],
        17: [6, 30, 54, 78],
        18: [6, 30, 56, 82],
        19: [6, 30, 58, 86],
        20: [6, 34, 62, 90],
    };

    function remainderBits(version) {
        if (version >= 2 && version <= 6) {
            return 7;
        }
        if (version >= 14 && version <= 20) {
            return 3;
        }
        return 0;
    }

    function bytesOf(text) {
        return Array.from(new TextEncoder().encode(text));
    }

    function dataCapacity(version) {
        var spec = BLOCKS[version];
        return spec[1] * spec[2] + spec[3] * spec[4];
    }

    function chooseVersion(byteLength) {
        for (var version = 1; version <= 20; version++) {
            var bits = dataCapacity(version) * 8;
            var header = 4 + (version < 10 ? 8 : 16);
            if (header + byteLength * 8 + 4 <= bits) {
                return version;
            }
        }
        throw new Error('Teks QR terlalu panjang.');
    }

    function pushBits(bits, value, length) {
        for (var i = length - 1; i >= 0; i--) {
            bits.push((value >>> i) & 1);
        }
    }

    function bitsToCodewords(bits, total) {
        while (bits.length % 8 !== 0) {
            bits.push(0);
        }
        var words = [];
        for (var i = 0; i < bits.length; i += 8) {
            var value = 0;
            for (var j = 0; j < 8; j++) {
                value = (value << 1) | bits[i + j];
            }
            words.push(value);
        }
        var pads = [0xec, 0x11];
        var p = 0;
        while (words.length < total) {
            words.push(pads[p % 2]);
            p += 1;
        }
        return words;
    }

    function interleave(version, dataWords) {
        var spec = BLOCKS[version];
        var ecPer = spec[0];
        var blocks = [];
        var offset = 0;
        function take(count, dataLen) {
            for (var i = 0; i < count; i++) {
                var data = dataWords.slice(offset, offset + dataLen);
                offset += dataLen;
                blocks.push({ data: data, ecc: rsEncode(data, ecPer) });
            }
        }
        take(spec[1], spec[2]);
        take(spec[3], spec[4]);

        var result = [];
        var maxData = Math.max.apply(null, blocks.map(function (b) { return b.data.length; }));
        var i, b;
        for (i = 0; i < maxData; i++) {
            for (b = 0; b < blocks.length; b++) {
                if (i < blocks[b].data.length) {
                    result.push(blocks[b].data[i]);
                }
            }
        }
        for (i = 0; i < ecPer; i++) {
            for (b = 0; b < blocks.length; b++) {
                result.push(blocks[b].ecc[i]);
            }
        }
        return result;
    }

    function sizeOf(version) {
        return 21 + (version - 1) * 4;
    }

    function inFinder(x, y, size) {
        return (x < 9 && y < 9) || (x >= size - 8 && y < 9) || (x < 9 && y >= size - 8);
    }

    function fillFinder(modules, reserved, ox, oy) {
        var x, y, dx, dy, dist;
        for (y = -1; y <= 7; y++) {
            for (x = -1; x <= 7; x++) {
                dx = ox + x;
                dy = oy + y;
                if (dy < 0 || dx < 0 || dy >= modules.length || dx >= modules.length) {
                    continue;
                }
                dist = Math.max(Math.abs(x - 3), Math.abs(y - 3));
                modules[dy][dx] = x >= 0 && x <= 6 && y >= 0 && y <= 6 && (dist === 3 || dist <= 1);
                reserved[dy][dx] = true;
            }
        }
    }

    function fillAlign(modules, reserved, positions) {
        var i, j, x, y, px, py, dx, dy, dist;
        for (i = 0; i < positions.length; i++) {
            for (j = 0; j < positions.length; j++) {
                px = positions[i];
                py = positions[j];
                if (reserved[py][px]) {
                    continue;
                }
                for (dy = -2; dy <= 2; dy++) {
                    for (dx = -2; dx <= 2; dx++) {
                        x = px + dx;
                        y = py + dy;
                        dist = Math.max(Math.abs(dx), Math.abs(dy));
                        modules[y][x] = dist === 2 || dist === 0;
                        reserved[y][x] = true;
                    }
                }
            }
        }
    }

    function drawFunction(modules, reserved, version) {
        var size = modules.length;
        var i;
        fillFinder(modules, reserved, 0, 0);
        fillFinder(modules, reserved, size - 7, 0);
        fillFinder(modules, reserved, 0, size - 7);
        for (i = 0; i < size; i++) {
            if (! reserved[6][i]) {
                modules[6][i] = i % 2 === 0;
                reserved[6][i] = true;
            }
            if (! reserved[i][6]) {
                modules[i][6] = i % 2 === 0;
                reserved[i][6] = true;
            }
        }
        if (ALIGN[version]) {
            fillAlign(modules, reserved, ALIGN[version]);
        }
        reserved[size - 8][8] = true;
        modules[size - 8][8] = true;
        for (i = 0; i < 9; i++) {
            reserved[8][i] = true;
            reserved[i][8] = true;
        }
        for (i = 0; i < 8; i++) {
            reserved[8][size - 1 - i] = true;
            reserved[size - 1 - i][8] = true;
        }
        if (version >= 7) {
            var r, c;
            for (r = 0; r < 6; r++) {
                for (c = 0; c < 3; c++) {
                    reserved[r][size - 11 + c] = true;
                    reserved[size - 11 + c][r] = true;
                }
            }
        }
    }

    function bchVersion(version) {
        var d = version << 12;
        for (var i = 5; i >= 0; i--) {
            if ((d >>> (i + 12)) & 1) {
                d ^= 0x1f25 << i;
            }
        }
        return (version << 12) | (d & 0xfff);
    }

    function drawVersion(modules, version) {
        if (version < 7) {
            return;
        }
        var bits = bchVersion(version);
        var size = modules.length;
        var i, bit, r, c;
        for (i = 0; i < 18; i++) {
            bit = ((bits >> i) & 1) === 1;
            r = Math.floor(i / 3);
            c = size - 11 + (i % 3);
            modules[r][c] = bit;
            modules[c][r] = bit;
        }
    }

    function maskFn(id, x, y) {
        switch (id) {
            case 0: return (x + y) % 2 === 0;
            case 1: return y % 2 === 0;
            case 2: return x % 3 === 0;
            case 3: return (x + y) % 3 === 0;
            case 4: return (Math.floor(y / 2) + Math.floor(x / 3)) % 2 === 0;
            case 5: return (x * y) % 2 + (x * y) % 3 === 0;
            case 6: return ((x * y) % 2 + (x * y) % 3) % 2 === 0;
            default: return ((x + y) % 2 + (x * y) % 3) % 2 === 0;
        }
    }

    function placeData(modules, reserved, bits) {
        var size = modules.length;
        var bit = 0;
        var upward = true;
        var x, y, col, pair, dx;
        for (col = size - 1; col > 0; col -= 2) {
            if (col === 6) {
                col -= 1;
            }
            for (pair = 0; pair < size; pair++) {
                y = upward ? size - 1 - pair : pair;
                for (dx = 0; dx < 2; dx++) {
                    x = col - dx;
                    if (reserved[y][x]) {
                        continue;
                    }
                    modules[y][x] = bit < bits.length ? bits[bit] === 1 : false;
                    bit += 1;
                }
            }
            upward = ! upward;
        }
    }

    function cloneGrid(grid) {
        return grid.map(function (row) {
            return row.slice();
        });
    }

    function applyMask(modules, reserved, id) {
        var masked = cloneGrid(modules);
        var size = modules.length;
        for (var y = 0; y < size; y++) {
            for (var x = 0; x < size; x++) {
                if (! reserved[y][x] && maskFn(id, x, y)) {
                    masked[y][x] = ! masked[y][x];
                }
            }
        }
        return masked;
    }

    function bchFormat(eclMask) {
        var d = eclMask << 10;
        for (var i = 4; i >= 0; i--) {
            if ((d >>> (i + 10)) & 1) {
                d ^= 0x537 << i;
            }
        }
        return ((eclMask << 10) | (d & 0x3ff)) ^ 0x5412;
    }

    function drawFormat(modules, reserved, maskId) {
        var bits = bchFormat((0 << 3) | maskId);
        var size = modules.length;
        var i, bit;
        for (i = 0; i < 15; i++) {
            bit = ((bits >> i) & 1) === 1;
            if (i < 6) {
                modules[i][8] = bit;
                reserved[i][8] = true;
            } else if (i < 8) {
                modules[i + 1][8] = bit;
                reserved[i + 1][8] = true;
            } else {
                modules[size - 15 + i][8] = bit;
                reserved[size - 15 + i][8] = true;
            }
            if (i < 8) {
                modules[8][size - 1 - i] = bit;
                reserved[8][size - 1 - i] = true;
            } else if (i < 9) {
                modules[8][15 - i] = bit;
                reserved[8][15 - i] = true;
            } else {
                modules[8][14 - i] = bit;
                reserved[8][14 - i] = true;
            }
        }
        reserved[8][8] = true;
    }

    function scoreMask(modules) {
        var size = modules.length;
        var score = 0;
        var x, y, run, count, dark = 0;
        function same4(a, b, c, d) {
            return a === b && b === c && c === d;
        }
        for (y = 0; y < size; y++) {
            run = 1;
            for (x = 1; x < size; x++) {
                if (modules[y][x] === modules[y][x - 1]) {
                    run += 1;
                } else {
                    if (run >= 5) {
                        score += 3 + (run - 5);
                    }
                    run = 1;
                }
            }
            if (run >= 5) {
                score += 3 + (run - 5);
            }
        }
        for (x = 0; x < size; x++) {
            run = 1;
            for (y = 1; y < size; y++) {
                if (modules[y][x] === modules[y - 1][x]) {
                    run += 1;
                } else {
                    if (run >= 5) {
                        score += 3 + (run - 5);
                    }
                    run = 1;
                }
            }
            if (run >= 5) {
                score += 3 + (run - 5);
            }
        }
        for (y = 0; y < size - 1; y++) {
            for (x = 0; x < size - 1; x++) {
                if (same4(modules[y][x], modules[y][x + 1], modules[y + 1][x], modules[y + 1][x + 1])) {
                    score += 3;
                }
            }
        }
        var finder = [true, false, true, true, true, false, true];
        function hasFinder(seq) {
            for (var s = 0; s <= seq.length - 7; s++) {
                var ok = true;
                for (var k = 0; k < 7; k++) {
                    if (seq[s + k] !== finder[k]) {
                        ok = false;
                        break;
                    }
                }
                if (ok) {
                    return true;
                }
            }
            return false;
        }
        for (y = 0; y < size; y++) {
            if (hasFinder(modules[y])) {
                score += 40;
            }
        }
        for (x = 0; x < size; x++) {
            var col = [];
            for (y = 0; y < size; y++) {
                col.push(modules[y][x]);
            }
            if (hasFinder(col)) {
                score += 40;
            }
        }
        for (y = 0; y < size; y++) {
            for (x = 0; x < size; x++) {
                if (modules[y][x]) {
                    dark += 1;
                }
            }
        }
        var percent = (dark * 100) / (size * size);
        score += Math.floor(Math.abs(percent - 50) / 5) * 10;
        return score;
    }

    function encodeMatrix(text) {
        var payload = bytesOf(text);
        var version = chooseVersion(payload.length);
        var bits = [];
        pushBits(bits, 0x4, 4);
        pushBits(bits, payload.length, version < 10 ? 8 : 16);
        for (var i = 0; i < payload.length; i++) {
            pushBits(bits, payload[i], 8);
        }
        var capacityBits = dataCapacity(version) * 8;
        var remain = Math.min(4, capacityBits - bits.length);
        for (i = 0; i < remain; i++) {
            bits.push(0);
        }
        var dataWords = bitsToCodewords(bits, dataCapacity(version));
        var interleaved = interleave(version, dataWords);
        var stream = [];
        for (i = 0; i < interleaved.length; i++) {
            pushBits(stream, interleaved[i], 8);
        }
        for (i = 0; i < remainderBits(version); i++) {
            stream.push(0);
        }

        var size = sizeOf(version);
        var modules = Array.from({ length: size }, function () {
            return new Array(size).fill(false);
        });
        var reserved = Array.from({ length: size }, function () {
            return new Array(size).fill(false);
        });
        drawFunction(modules, reserved, version);
        placeData(modules, reserved, stream);

        var best = null;
        var bestScore = Infinity;
        for (var mask = 0; mask < 8; mask++) {
            var masked = applyMask(modules, reserved, mask);
            drawFormat(masked, cloneGrid(reserved), mask);
            drawVersion(masked, version);
            var score = scoreMask(masked);
            if (score < bestScore) {
                bestScore = score;
                best = masked;
            }
        }
        return best;
    }

    function toCanvas(canvas, text, size) {
        var matrix = encodeMatrix(String(text));
        var n = matrix.length;
        var px = size || canvas.width || 160;
        canvas.width = px;
        canvas.height = px;
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, px, px);
        var quiet = 2;
        var cell = px / (n + quiet * 2);
        ctx.fillStyle = '#071422';
        for (var y = 0; y < n; y++) {
            for (var x = 0; x < n; x++) {
                if (matrix[y][x]) {
                    ctx.fillRect((x + quiet) * cell, (y + quiet) * cell, cell, cell);
                }
            }
        }
    }

    root.PergabiQr = {
        toCanvas: toCanvas,
    };
}(window));
