// resources/js/app.js

import './bootstrap';

import Alpine from 'alpinejs';
// ★★★ここを修正★★★
import { createIcons, icons } from 'lucide'; // createIcons と共に icons もインポートする
// ★★★ここまで修正★★★

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons }); // icons オブジェクトを引数として渡す
});