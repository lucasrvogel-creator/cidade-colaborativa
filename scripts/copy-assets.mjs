import { mkdir, cp } from 'node:fs/promises';
const items = [
  ['bootstrap/dist/css', 'bootstrap/css'], ['bootstrap/dist/js', 'bootstrap/js'],
  ['bootstrap-icons/font', 'icons'], ['leaflet/dist', 'leaflet'], ['chart.js/dist', 'chart'],
];
for (const [source, target] of items) {
  await mkdir(`public/assets/vendor/${target}`, { recursive: true });
  await cp(`node_modules/${source}`, `public/assets/vendor/${target}`, { recursive: true });
}
console.log('Bibliotecas copiadas para public/assets/vendor.');
