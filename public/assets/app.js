/* Small enhancements. The site works fine with JavaScript switched off —
   every one of these is a convenience, not a requirement.
   Sinhala: මේවා අමතර පහසුකම්. JS off කරත් site එක වැඩ කරනවා. */
(function () {
  'use strict';

  // 1. Build the link (slug) from the title while you type,
  //    but stop as soon as the author edits the slug by hand.
  var title = document.getElementById('title');
  var slug  = document.getElementById('slug');

  if (title && slug) {
    var touched = slug.value.trim() !== '';
    slug.addEventListener('input', function () { touched = true; });

    title.addEventListener('input', function () {
      if (touched) return;
      slug.value = title.value
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    });
  }

  // 2. Live word count under the editor.
  var body  = document.getElementById('body');
  var count = document.getElementById('wordcount');

  if (body && count) {
    var update = function () {
      var words = body.value.trim().split(/\s+/).filter(Boolean).length;
      count.textContent = words + (words === 1 ? ' word' : ' words');
    };
    body.addEventListener('input', update);
    update();
  }

  // 3. Confirm before a destructive POST.
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!window.confirm(form.dataset.confirm)) {
        event.preventDefault();
      }
    });
  });
})();
