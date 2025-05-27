import 'bootstrap'; // if using Bootstrap JS
import 'datatables.net';
import 'datatables.net-bs5';


import toastr from 'toastr';
import Alpine from 'alpinejs';

window.toastr = toastr;
window.Alpine = Alpine;
Alpine.start();

// Import your custom script
import './googleSynced.js';
// import './createForm.js';
