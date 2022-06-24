<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,initial-scale=1.0" /><title>Namespace edit counter</title>
<link rel="stylesheet" href="style.css" />
<!-- I'm going to reuse some CSS I wrote earlier -->
</head><body id="baudy">
<form action="javascript:;" onsubmit="query()">
    <label>en.wp username: <input id="username" disabled="disabled" /></label>
    <input value="submit" type="submit" />
    </form>
    <div id="editcount" hidden="hidden">
    Edit count: <span id="count">0</span>
    <span id="still">(still counting)</span>
    <table>
        <caption>Most edited namespaces</caption>
        <thead>
            <th>Namespace</th>
            <th>Total edits</th>
            <th>Non-reverted edits</th>
        </thead>
        <tbody id="namespaces"></tbody>
    </table>
    </div>
    <script>
    fetch(`https://en.wikipedia.org/w/api.php?format=json&origin=*&action=query&meta=siteinfo&siprop=namespaces`)
    .then((res) => res.json())
    .then(function(json) {
      window.namespaces = json.query.namespaces;
      document.querySelector('#username').disabled = '';
    });
        function query(cont = '', nses = {}, namespaces = null) {
	document.querySelector('#still').hidden = '';
	if (!cont) globalThis.count = 0;
	if (cont) conti = `&uccontinue=${encodeURIComponent(cont)}`;
  else conti = '';
	fetch(`https://en.wikipedia.org/w/api.php?origin=*&action=query&format=json&list=usercontribs&uclimit=500${conti}&ucuser=${encodeURIComponent(document.querySelector('#username').value)}&ucprop=ids%7Ctitle%7Ctimestamp%7Ccomment%7Csize%7Cflags%7Ctags`)
  .then((res) => res.json())
  .then(function(json) {
  	document.querySelector('#editcount').hidden = '';
  	globalThis.count += json.query.usercontribs.length;
    document.querySelector('#count').textContent = globalThis.count;
    json.query.usercontribs.forEach(function(contrib) {
        var namespace = window.namespaces[contrib.ns].canonical;
        if (!namespace) namespace = 'Mainspace';

        if (!nses[namespace]) nses[namespace] = {total: 0, nonReverted: 0};
        nses[namespace]['total']++;
        if (!contrib.tags.includes('mw-reverted')) nses[namespace]['nonReverted']++;
        putNamespaces(nses);
    })
    if (!json.continue) return document.querySelector('#still').hidden = 'hidden';
    const contin = json.continue.uccontinue;
    query(contin, nses);
  });
}
function putNamespaces(namespaces) {
    console.log(namespaces);
    namespaces = sort(namespaces);
    document.querySelector('#namespaces').innerHTML = '';
    const nses = Object.keys(namespaces);
    nses.forEach(function(ns) {
        const n = document.createElement('tr');
        const name = document.createElement('th');
        name.scope = 'row';
        name.textContent = ns;
        n.appendChild(name);
        const c = document.createElement('td');
        c.textContent = namespaces[ns]['total'];
        n.appendChild(c);
        const nrv = document.createElement('td');
        nrv.textContent = namespaces[ns]['nonReverted'];
        n.appendChild(nrv);
        document.querySelector('tbody').appendChild(n);
    })
}
function sort(obj) {
    let sortable = [];
for (var thing in obj) {
    sortable.push([thing, obj[thing]]);
}

sortable.sort(function(a, b) {
    return b[1]['total'] - a[1]['total'];
});
const object = {};
sortable.forEach(function(s) {object[s[0]] = s[1]})
return object;
}
// Code from https://stackoverflow.com/a/14268260/15578194,
// licensed under CC BY SA 3.0
function makeSortable(table) {
    var th = table.tHead, i;
    th && (th = th.rows[0]) && (th = th.cells);
    if (th) i = th.length;
    else return; // if no `<thead>` then do nothing
    while (--i >= 0) (function (i) {
        var dir = 1;
        th[i].addEventListener('click', function () {sortTable(table, i, (dir = 1 - dir))});
    }(i));
}
makeSortable(document.querySelector('table'));
function sortTable(table, col, reverse) {
    var tb = table.tBodies[0], // use `<tbody>` to ignore `<thead>` and `<tfoot>` rows
        tr = Array.prototype.slice.call(tb.rows, 0), // put rows into array
        i;
    reverse = -((+reverse) || -1);
    tr = tr.sort(function (a, b) { // sort rows
        return reverse // `-1 *` if want opposite order
            * (a.cells[col].textContent.trim() // using `.textContent.trim()` for test
                .localeCompare(b.cells[col].textContent.trim(), 'en', {numeric: true})
                );
    });
    for(i = 0; i < tr.length; ++i) tb.appendChild(tr[i]); // append each row in order
}
    </script>
    <p>Made by <a href="https://en.wikipedia.org/wiki/user:weeklyd3">weeklyd3</a>. Licensed under the GNU General Public License.</p>
<p><a href="https://github.com/weeklyd3/non-reverted-edit-counter">Source code available</a> under GPL 3.0.</p>
    </body>
    </html>
