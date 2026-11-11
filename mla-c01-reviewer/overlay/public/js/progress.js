/* Progress tracking in the learner's own browser (localStorage). No data leaves the device. */
(function (global) {
  'use strict';

  var KEY = 'mla-c01-reviewer:v1';

  function blank() {
    return { answered: 0, correct: 0, missed: {}, topics: {} };
  }

  function load() {
    try {
      var raw = global.localStorage.getItem(KEY);
      if (!raw) return blank();
      var data = JSON.parse(raw);
      if (!data || typeof data !== 'object') return blank();
      return {
        answered: +data.answered || 0,
        correct: +data.correct || 0,
        missed: data.missed && typeof data.missed === 'object' ? data.missed : {},
        topics: data.topics && typeof data.topics === 'object' ? data.topics : {}
      };
    } catch (e) {
      return blank(); // storage blocked or corrupt: behave as a fresh visitor
    }
  }

  function save(data) {
    try { global.localStorage.setItem(KEY, JSON.stringify(data)); } catch (e) { /* ignore */ }
  }

  /** Record one answered question. A correct answer clears it from the "missed" list. */
  function recordAnswer(id, wasCorrect) {
    var d = load();
    d.answered += 1;
    if (wasCorrect) {
      d.correct += 1;
      delete d.missed[id];
    } else {
      d.missed[id] = true;
    }
    save(d);
  }

  /** Record a finished round for a topic (or "all"/"quick"). */
  function recordRound(topicKey, score, total) {
    if (!total) return;
    var d = load();
    var pct = Math.round((score / total) * 100);
    var t = d.topics[topicKey] || { attempts: 0, best: 0, last: 0 };
    t.attempts += 1;
    t.last = pct;
    t.best = Math.max(t.best, pct);
    d.topics[topicKey] = t;
    save(d);
  }

  function missedIds() {
    return Object.keys(load().missed).map(Number);
  }

  function reset() {
    try { global.localStorage.removeItem(KEY); } catch (e) { /* ignore */ }
  }

  /** Fill in the progress panel and per-topic "best" labels on the home page. */
  function renderHome() {
    var d = load();
    var panel = document.getElementById('progress');
    if (panel && d.answered > 0) {
      panel.hidden = false;
      var acc = Math.round((d.correct / d.answered) * 100);
      var set = function (name, val) {
        var el = panel.querySelector('[data-stat="' + name + '"]');
        if (el) el.textContent = val;
      };
      set('answered', d.answered);
      set('accuracy', acc + '%');
      set('missed', Object.keys(d.missed).length);
    }
    var bests = document.querySelectorAll('[data-best]');
    for (var i = 0; i < bests.length; i++) {
      var t = d.topics[bests[i].getAttribute('data-best')];
      if (t) bests[i].textContent = 'Best: ' + t.best + '%';
    }
  }

  global.MlaProgress = {
    load: load,
    recordAnswer: recordAnswer,
    recordRound: recordRound,
    missedIds: missedIds,
    reset: reset,
    renderHome: renderHome
  };
})(window);
