/**
 * House of Bungalow — mandatory image crop gate.
 *
 * Any <input type="file" accept="image/*"> carrying a data-crop-ratio="W:H"
 * attribute is intercepted on selection. If the chosen image's aspect ratio
 * doesn't already match that ratio (within a small tolerance), a crop modal
 * opens and the field is cleared until the user finishes cropping — there is
 * no way to submit the original, unmatched file. Video inputs are untouched.
 */
(function () {
  var RATIO_TOLERANCE = 0.02; // 2% — near-matches skip the modal
  var MIN_BOX = 40; // smallest crop box, in displayed (CSS) pixels

  function parseRatio(str) {
    var parts = String(str || '').split(':');
    var w = parseFloat(parts[0]);
    var h = parseFloat(parts[1]);
    if (!w || !h) return null;
    return w / h;
  }

  // ---- Modal (built once, reused for every crop) ------------------------
  var modal, imgEl, boxEl;
  var pendingInput = null;
  var pendingRatio = 1;
  var pendingMime = 'image/jpeg';
  var pendingName = 'image.jpg';
  var naturalW = 0, naturalH = 0;
  var drag = null; // {mode:'move'|'resize', startX, startY, boxX, boxY, boxW, boxH}

  function buildModal() {
    modal = document.createElement('div');
    modal.className = 'crop-modal';
    modal.innerHTML =
      '<div class="crop-modal-inner">' +
        '<h3>Crop This Image</h3>' +
        '<p class="crop-modal-help">This photo doesn’t match the shape needed for this spot. Drag inside the box to move it, drag the corner handle to resize, then crop — the original file won’t be used.</p>' +
        '<div class="crop-stage">' +
          '<img class="crop-img" alt="">' +
          '<div class="crop-box"><span class="crop-handle"></span></div>' +
        '</div>' +
        '<div class="crop-modal-actions">' +
          '<button type="button" class="btn btn-ghost crop-cancel">Cancel (won’t upload)</button>' +
          '<button type="button" class="btn crop-apply">Crop &amp; Use This Image</button>' +
        '</div>' +
      '</div>';
    document.body.appendChild(modal);

    imgEl = modal.querySelector('.crop-img');
    boxEl = modal.querySelector('.crop-box');

    modal.querySelector('.crop-cancel').addEventListener('click', cancelCrop);
    modal.querySelector('.crop-apply').addEventListener('click', applyCrop);

    boxEl.addEventListener('mousedown', function (e) { startDrag(e, 'move'); });
    boxEl.addEventListener('touchstart', function (e) { startDrag(e.touches[0], 'move'); e.preventDefault(); }, { passive: false });
    boxEl.querySelector('.crop-handle').addEventListener('mousedown', function (e) { e.stopPropagation(); startDrag(e, 'resize'); });
    boxEl.querySelector('.crop-handle').addEventListener('touchstart', function (e) { e.stopPropagation(); startDrag(e.touches[0], 'resize'); e.preventDefault(); }, { passive: false });

    document.addEventListener('mousemove', onDrag);
    document.addEventListener('touchmove', function (e) { if (drag) { onDrag(e.touches[0]); e.preventDefault(); } }, { passive: false });
    document.addEventListener('mouseup', endDrag);
    document.addEventListener('touchend', endDrag);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('open')) cancelCrop();
    });
  }

  // Bounds of the actually-rendered <img>, relative to .crop-stage (its
  // offsetParent) — NOT the full stage box, which can letterbox the image.
  function imgBounds() {
    return { x: imgEl.offsetLeft, y: imgEl.offsetTop, w: imgEl.offsetWidth, h: imgEl.offsetHeight };
  }

  function startDrag(e, mode) {
    var rect = boxEl.getBoundingClientRect();
    drag = {
      mode: mode,
      startX: e.clientX,
      startY: e.clientY,
      boxX: boxEl.offsetLeft,
      boxY: boxEl.offsetTop,
      boxW: rect.width,
      boxH: rect.height
    };
  }

  function onDrag(e) {
    if (!drag || !e) return;
    var dx = e.clientX - drag.startX;
    var dy = e.clientY - drag.startY;
    var b = imgBounds();

    if (drag.mode === 'move') {
      var x = Math.min(Math.max(drag.boxX + dx, b.x), b.x + b.w - drag.boxW);
      var y = Math.min(Math.max(drag.boxY + dy, b.y), b.y + b.h - drag.boxH);
      boxEl.style.left = x + 'px';
      boxEl.style.top = y + 'px';
    } else {
      // Resize from top-left corner, locked to pendingRatio, clamped to the image.
      var maxW = (b.x + b.w) - drag.boxX;
      var maxH = (b.y + b.h) - drag.boxY;
      var newW = Math.min(Math.max(drag.boxW + dx, MIN_BOX), maxW);
      var newH = newW / pendingRatio;
      if (newH > maxH) { newH = maxH; newW = newH * pendingRatio; }
      if (newW < MIN_BOX) { newW = MIN_BOX; newH = newW / pendingRatio; }
      boxEl.style.width = newW + 'px';
      boxEl.style.height = newH + 'px';
    }
  }

  function endDrag() { drag = null; }

  function resetBoxToCenter() {
    var b = imgBounds();
    var w, h;
    if (b.w / b.h > pendingRatio) {
      h = b.h;
      w = h * pendingRatio;
    } else {
      w = b.w;
      h = w / pendingRatio;
    }
    // Shrink slightly so the resize handle stays reachable within the image.
    w *= 0.92; h *= 0.92;
    boxEl.style.width = w + 'px';
    boxEl.style.height = h + 'px';
    boxEl.style.left = (b.x + (b.w - w) / 2) + 'px';
    boxEl.style.top = (b.y + (b.h - h) / 2) + 'px';
  }

  function openModal(input, file, ratio) {
    pendingInput = input;
    pendingRatio = ratio;
    pendingMime = (file.type === 'image/png' || file.type === 'image/webp' || file.type === 'image/gif') ? file.type : 'image/jpeg';
    pendingName = file.name || 'image.jpg';

    var reader = new FileReader();
    reader.onload = function (e) {
      imgEl.onload = function () {
        naturalW = imgEl.naturalWidth;
        naturalH = imgEl.naturalHeight;
        modal.classList.add('open');
        document.body.classList.add('scroll-locked');
        // Wait a frame so the stage has real layout dimensions.
        window.requestAnimationFrame(resetBoxToCenter);
      };
      imgEl.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  function closeModal() {
    modal.classList.remove('open');
    document.body.classList.remove('scroll-locked');
    pendingInput = null;
  }

  function cancelCrop() {
    if (pendingInput) pendingInput.value = '';
    closeModal();
  }

  function applyCrop() {
    if (!pendingInput) return;
    var imgRect = imgEl.getBoundingClientRect();
    var boxRect = boxEl.getBoundingClientRect();

    // Displayed-image → natural-image pixel scale.
    var scale = naturalW / imgRect.width;

    var sx = (boxRect.left - imgRect.left) * scale;
    var sy = (boxRect.top - imgRect.top) * scale;
    var sw = boxRect.width * scale;
    var sh = boxRect.height * scale;

    // Clamp fully inside the natural image (guards sub-pixel drift).
    sx = Math.max(0, Math.min(sx, naturalW - 1));
    sy = Math.max(0, Math.min(sy, naturalH - 1));
    sw = Math.max(1, Math.min(sw, naturalW - sx));
    sh = Math.max(1, Math.min(sh, naturalH - sy));

    var outW = Math.round(sw);
    var outH = Math.round(sh);
    var MAX_DIM = 2400;
    if (outW > MAX_DIM || outH > MAX_DIM) {
      var scaleDown = MAX_DIM / Math.max(outW, outH);
      outW = Math.round(outW * scaleDown);
      outH = Math.round(outH * scaleDown);
    }

    var canvas = document.createElement('canvas');
    canvas.width = outW;
    canvas.height = outH;
    var ctx = canvas.getContext('2d');
    ctx.drawImage(imgEl, sx, sy, sw, sh, 0, 0, outW, outH);

    var input = pendingInput;
    var name = pendingName;
    var mime = pendingMime;
    canvas.toBlob(function (blob) {
      if (!blob) { cancelCrop(); return; }
      var dt = new DataTransfer();
      var croppedFile = new File([blob], name, { type: mime, lastModified: Date.now() });
      dt.items.add(croppedFile);
      input.files = dt.files;
      closeModal();
    }, mime, 0.92);
  }

  // ---- Wire up every qualifying file input -------------------------------
  function handleChange(e) {
    var input = e.target;
    var ratioAttr = input.getAttribute('data-crop-ratio');
    if (!ratioAttr || !input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.type.indexOf('image/') !== 0) return;

    var target = parseRatio(ratioAttr);
    if (!target) return;

    if (!modal) buildModal();

    var probe = new Image();
    var url = URL.createObjectURL(file);
    probe.onload = function () {
      URL.revokeObjectURL(url);
      if (!probe.naturalWidth || !probe.naturalHeight) {
        return; // can't read real dimensions (e.g. a sizeless SVG) — skip the gate
      }
      var actual = probe.naturalWidth / probe.naturalHeight;
      if (Math.abs(actual - target) / target <= RATIO_TOLERANCE) {
        return; // close enough — let it upload as-is
      }
      openModal(input, file, target);
    };
    probe.onerror = function () {
      URL.revokeObjectURL(url);
      // Unreadable as an image — let normal server-side validation catch it.
    };
    probe.src = url;
  }

  document.addEventListener('change', function (e) {
    if (e.target && e.target.tagName === 'INPUT' && e.target.type === 'file') {
      handleChange(e);
    }
  });
})();
