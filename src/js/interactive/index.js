import Alpine from 'alpinejs';
import { bootAlpine } from '../alpine/bootstrap.js';
import { mobileNav } from '../alpine/components/mobile-nav.js';
import { bookGallery } from '../alpine/components/book-gallery.js';
import { yearRange } from '../alpine/components/year-range.js';
import { archiveToolbar } from '../alpine/components/archive-toolbar.js';

bootAlpine(Alpine, function (alpine) {
	alpine.data('mobileNav', mobileNav);
	alpine.data('bookGallery', bookGallery);
	alpine.data('yearRange', yearRange);
	alpine.data('archiveToolbar', archiveToolbar);
});
