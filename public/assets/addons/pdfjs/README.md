PDF.js 6.5.438 (Mozilla), Apache-2.0. See LICENSE.

The legacy build includes compatibility polyfills needed by supported browsers.
The matching worker, CMaps, fonts and WASM decoders are served locally.

After updating pdfjs-dist, copy legacy/build/pdf.mjs and
legacy/build/pdf.worker.mjs here, together with cmaps/, standard_fonts/, wasm/
and LICENSE from the package root. Keep .htaccess for Apache module MIME types.

Validate the renderer with: node --test tests/work-previews.test.mjs
