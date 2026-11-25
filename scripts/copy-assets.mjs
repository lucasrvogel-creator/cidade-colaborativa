import { mkdir, cp } from 'node:fs/promises';

const files = [
  ['bootstrap/dist/css/bootstrap.min.css', 'bootstrap/css/bootstrap.min.css'],
  ['bootstrap/dist/js/bootstrap.bundle.min.js', 'bootstrap/js/bootstrap.bundle.min.js'],
  ['bootstrap-icons/font/bootstrap-icons.min.css', 'icons/bootstrap-icons.min.css'],
  ['bootstrap-icons/font/fonts', 'icons/fonts'],
  ['bootstrap-icons/LICENSE', 'icons/LICENSE'],
  ['leaflet/dist/leaflet.css', 'leaflet/leaflet.css'],
  ['leaflet/dist/leaflet.js', 'leaflet/leaflet.js'],
  ['leaflet/dist/images', 'leaflet/images'],
  ['leaflet/LICENSE', 'leaflet/LICENSE'],
  ['chart.js/dist/chart.umd.min.js', 'chart/chart.umd.min.js'],
  ['chart.js/LICENSE.md', 'chart/LICENSE.md'],
  ['bootstrap/LICENSE', 'bootstrap/LICENSE'],
];

for (const [source, target] of files) {
  const destination = `public/assets/vendor/${target}`;
  await mkdir(destination.slice(0, destination.lastIndexOf('/')), { recursive: true });
  await cp(`node_modules/${source}`, destination, { recursive: true });
}
console.log('Bibliotecas copiadas para public/assets/vendor.');
