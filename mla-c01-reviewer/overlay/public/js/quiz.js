/* Quiz engine. Questions come from /question-bank (resources/data/questions.json). */
(function () {
  'use strict';

  var root = document.getElementById('quiz-app');
  if (!root) return;

  var LETTERS = 'ABCD';
  var bank = [];
  var topics = [];
  var session = null; // {key, label, list, idx, score, missed, answered, optOrder}

  function esc(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function shuffle(a) {
    a = a.slice();
    for (var i = a.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var t = a[i]; a[i] = a[j]; a[j] = t;
    }
    return a;
  }

  function buildTopics() {
    var map = {};
    topics = [];
    bank.forEach(function (q) {
      if (!map[q.topic_slug]) {
        map[q.topic_slug] = { slug: q.topic_slug, name: q.topic, count: 0 };
        topics.push(map[q.topic_slug]);
      }
      map[q.topic_slug].count++;
    });
  }

  /* ---------- screens ---------- */

  function menu() {
    session = null;
    var prog = MlaProgress.load();
    var missedCount = Object.keys(prog.missed).length;
    var html = '<header class="page-head"><h1>Practice</h1>' +
      '<p>' + bank.length + ' questions across ' + topics.length + ' topics. Every answer choice is explained.</p></header>' +
      '<section class="block"><h2 class="block-title">Quick start</h2><div class="start-grid">' +
      startCard('quick', '⚡', 'Quick 10', 'Ten random questions, about 5 minutes') +
      startCard('all', '🔀', 'All questions', bank.length + ' questions, shuffled') +
      (missedCount ? startCard('missed', '🔁', 'Review missed', missedCount + ' question' + (missedCount === 1 ? '' : 's') + ' to revisit') : '') +
      '</div></section>' +
      '<section class="block"><h2 class="block-title">By topic <span class="count">' + topics.length + '</span></h2><div class="topic-grid">';
    topics.forEach(function (t, i) {
      var best = prog.topics[t.slug];
      html += '<button class="topic-card" data-start="topic:' + esc(t.slug) + '">' +
        '<span class="topic-num">' + (i < 9 ? '0' : '') + (i + 1) + '</span>' +
        '<span class="topic-body"><b>' + esc(t.name) + '</b><span>' + t.count + ' questions</span></span>' +
        (best ? '<span class="badge' + (best.best >= 80 ? ' good' : '') + '">Best ' + best.best + '%</span>' : '') +
        '</button>';
    });
    html += '</div></section>';
    root.innerHTML = html;
    var btns = root.querySelectorAll('[data-start]');
    Array.prototype.forEach.call(btns, function (b) {
      b.addEventListener('click', function () { startFromKey(b.getAttribute('data-start')); });
    });
  }

  function startCard(key, icon, title, sub) {
    return '<button class="start-card" data-start="' + esc(key) + '"><span class="start-ico" aria-hidden="true">' + icon +
      '</span><span class="topic-body"><b>' + esc(title) + '</b><span>' + esc(sub) + '</span></span></button>';
  }

  function startFromKey(key) {
    var list, label;
    if (key === 'all') {
      list = bank; label = 'All topics';
    } else if (key === 'quick') {
      list = shuffle(bank).slice(0, 10); label = 'Quick 10';
    } else if (key === 'missed') {
      var ids = MlaProgress.missedIds();
      list = bank.filter(function (q) { return ids.indexOf(q.id) !== -1; });
      label = 'Review missed';
    } else if (key.indexOf('topic:') === 0) {
      var slug = key.slice(6);
      list = bank.filter(function (q) { return q.topic_slug === slug; });
      var t = topics.filter(function (x) { return x.slug === slug; })[0];
      label = t ? t.name : slug;
    }
    if (!list || !list.length) { menu(); return; }
    begin(key.indexOf('topic:') === 0 ? key.slice(6) : key, label, list);
  }

  function begin(key, label, list) {
    session = { key: key, label: label, list: shuffle(list), idx: 0, score: 0, missed: [], answered: false, optOrder: [] };
    renderQuestion();
  }

  function renderQuestion() {
    var s = session;
    if (s.idx >= s.list.length) { return results(); }
    var q = s.list[s.idx];
    s.answered = false;
    s.optOrder = shuffle([0, 1, 2, 3]);
    var pct = Math.round((s.idx / s.list.length) * 100);
    var html = '<div class="quiz-top"><button class="link-btn" id="exit">← All topics</button>' +
      '<span class="score-pill">Score ' + s.score + '</span></div>' +
      '<div class="progressbar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + pct + '"><i style="width:' + pct + '%"></i></div>' +
      '<div class="meta"><span>' + esc(s.label) + '</span><span>Question ' + (s.idx + 1) + ' of ' + s.list.length + '</span></div>' +
      '<section class="panel qcard"><span class="topic-tag">' + esc(q.topic) + '</span>' +
      '<h2 class="q">' + esc(q.question) + '</h2><div id="opts">';
    s.optOrder.forEach(function (oi, k) {
      html += '<button class="opt" data-k="' + k + '"><div class="t"><span class="l">' + LETTERS[k] +
        '</span><span class="otext">' + esc(q.options[oi].text) + '</span></div><div class="why"></div></button>';
    });
    html += '</div><div id="fb"></div></section>' +
      '<p class="kbd-hint">Tip: press <kbd>A</kbd>–<kbd>D</kbd> or <kbd>1</kbd>–<kbd>4</kbd> to answer, <kbd>Enter</kbd> for next.</p>';
    root.innerHTML = html;
    var opts = root.querySelectorAll('.opt');
    Array.prototype.forEach.call(opts, function (b) {
      b.addEventListener('click', function () { pick(+b.getAttribute('data-k')); });
    });
    document.getElementById('exit').addEventListener('click', function () {
      if (s.score + s.missed.length === 0 || confirm('Leave this round? Your progress in it will be lost.')) menu();
    });
    window.scrollTo(0, 0);
  }

  function pick(k) {
    var s = session;
    if (s.answered) return;
    s.answered = true;
    var q = s.list[s.idx];
    var chosen = s.optOrder[k];
    var ok = chosen === q.correct;
    if (ok) s.score++; else s.missed.push(q);
    MlaProgress.recordAnswer(q.id, ok);

    var opts = root.querySelectorAll('.opt');
    Array.prototype.forEach.call(opts, function (b, i) {
      var oi = s.optOrder[i];
      var isCorrect = oi === q.correct;
      var isChosen = i === k;
      var tag, cls;
      if (isCorrect) { cls = 'correct'; tag = isChosen ? '✓ Your answer: correct' : '✓ Correct answer'; }
      else if (isChosen) { cls = 'wrong'; tag = '✗ Your answer: incorrect'; }
      else { cls = 'other'; tag = 'Why this one is not the answer'; }
      b.disabled = true;
      b.classList.add('show', cls);
      b.querySelector('.why').innerHTML = '<span class="tag">' + tag + '</span>' + esc(q.options[oi].why);
    });

    var last = s.idx === s.list.length - 1;
    document.getElementById('fb').innerHTML =
      '<div class="verdict ' + (ok ? 'ok' : 'no') + '">' +
      (ok ? 'Correct! Here is why each of the other choices is off:'
          : 'Not quite. The correct answer is highlighted in green, with the explanation below.') + '</div>' +
      '<div class="recap"><b>Key takeaway:</b> ' + esc(q.takeaway) + '</div>' +
      '<button class="btn primary" id="next">' + (last ? 'See results' : 'Next question →') + '</button>';
    document.getElementById('next').addEventListener('click', next);
    document.getElementById('fb').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function next() {
    session.idx++;
    renderQuestion();
  }

  function results() {
    var s = session;
    var total = s.list.length;
    var pct = Math.round((s.score / total) * 100);
    MlaProgress.recordRound(s.key, s.score, total);
    var msg = pct >= 80 ? 'Strong. Keep drilling the details.'
      : pct >= 60 ? 'Getting there. Re-read the topics you missed.'
      : 'Worth another pass through your notes before retrying.';
    var html = '<section class="panel results"><span class="topic-tag">Results · ' + esc(s.label) + '</span>' +
      '<div class="score-wrap"><div class="ring ' + (pct >= 80 ? 'good' : pct >= 60 ? 'mid' : 'low') + '" style="--p:' + pct + '"><span>' + pct + '%</span></div>' +
      '<div><div class="big">' + s.score + ' / ' + total + '</div><p class="muted">' + msg + '</p></div></div>';
    if (s.missed.length) {
      html += '<h2 class="block-title">Review these <span class="count">' + s.missed.length + '</span></h2>';
      s.missed.forEach(function (q) {
        html += '<div class="rev"><span class="topic-tag">' + esc(q.topic) + '</span><b>' + esc(q.question) + '</b><span>' + esc(q.takeaway) + '</span></div>';
      });
    } else {
      html += '<p class="verdict ok">No misses. Nice work.</p>';
    }
    html += '<p class="actions"><button class="btn primary" id="again">Retake</button>' +
      (s.missed.length ? '<button class="btn" id="retry">Retry missed only</button>' : '') +
      '<button class="btn" id="back">Back to topics</button></p></section>';
    root.innerHTML = html;
    var keep = { key: s.key, label: s.label, list: s.list, missed: s.missed.slice() };
    document.getElementById('again').addEventListener('click', function () { begin(keep.key, keep.label, keep.list); });
    var retry = document.getElementById('retry');
    if (retry) retry.addEventListener('click', function () { begin(keep.key, 'Retry: ' + keep.label, keep.missed); });
    document.getElementById('back').addEventListener('click', menu);
    window.scrollTo(0, 0);
  }

  /* ---------- keyboard: A–D or 1–4 to answer, Enter for next ---------- */
  document.addEventListener('keydown', function (e) {
    if (!session || e.ctrlKey || e.metaKey || e.altKey) return;
    var k = '1234'.indexOf(e.key);
    if (k === -1) k = 'abcd'.indexOf(String(e.key).toLowerCase());
    if (k !== -1 && !session.answered) { pick(k); return; }
    if (e.key === 'Enter' && session.answered) {
      var n = document.getElementById('next');
      if (n) { e.preventDefault(); n.click(); }
    }
  });

  /* ---------- boot ---------- */
  fetch(root.getAttribute('data-questions-url'), { headers: { Accept: 'application/json' } })
    .then(function (r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function (data) {
      bank = data;
      buildTopics();
      var params = new URLSearchParams(window.location.search);
      var topic = params.get('topic');
      var mode = params.get('mode');
      if (topic && topics.some(function (t) { return t.slug === topic; })) startFromKey('topic:' + topic);
      else if (mode === 'quick') startFromKey('quick');
      else if (mode === 'missed' && MlaProgress.missedIds().length) startFromKey('missed');
      else menu();
    })
    .catch(function () {
      root.innerHTML = '<p class="panel">Sorry, the questions could not be loaded. Please refresh the page.</p>';
    });
})();
