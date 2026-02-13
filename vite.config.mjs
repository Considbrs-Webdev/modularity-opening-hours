import { createViteConfig } from "vite-config-factory";

const entries = {
    'css/modularity-opening-hours': './source/sass/modularity-opening-hours.scss',
};

export default createViteConfig(entries, {
    outDir: "assets/dist",
    manifestFile: "manifest.json",
});
