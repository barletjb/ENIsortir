import { startStimulusApp } from '@symfony/stimulus-bundle';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
