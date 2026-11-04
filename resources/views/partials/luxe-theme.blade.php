{{-- Shared colour and type palette. Runs after each layout's own Tailwind config and
     re-tints the stock scales into one restrained set: forest, warm stone, brass, oxblood, ink navy. --}}
<script>
(() => {
    if (!window.tailwind) return;
    const scale = (v) => Object.fromEntries([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950].map((k, i) => [k, v[i]]));
    const forest = scale(['#f1f4f0', '#e1e8df', '#c5d3c3', '#9fb59d', '#6f8f70', '#4b6f4f', '#355a3c', '#284a30', '#1f3c27', '#183020', '#0e1d13']);
    const stone = scale(['#f8f6f1', '#f0ece4', '#e3ddd1', '#cfc7b8', '#a59d8e', '#7a7366', '#5e584d', '#47423a', '#2f2c27', '#1f1d1a', '#141311']);
    const brass = scale(['#faf5ea', '#f3e8cf', '#e8d5a8', '#d8bc7c', '#c4a15a', '#a8843f', '#8b6b31', '#6f5428', '#574222', '#46361d', '#2a200f']);
    const oxblood = scale(['#faf1ef', '#f3dfdb', '#e6bfb7', '#d3968a', '#b9695b', '#9e4637', '#85362a', '#6e2c23', '#5a261f', '#4b221c', '#2a100c']);
    const navy = scale(['#f0f3f7', '#dfe5ee', '#c1cbdb', '#97a7c0', '#6c80a0', '#4d6283', '#3b4e6c', '#2f3f58', '#283449', '#232c3d', '#151a25']);
    const plum = scale(['#f5f1f5', '#e9e0e9', '#d4c3d3', '#b79db5', '#95748f', '#785a73', '#61475d', '#4f3a4c', '#42323f', '#382b36', '#1f171e']);
    const petrol = scale(['#eff4f4', '#dce7e7', '#bbd0d0', '#8fb0b1', '#648b8d', '#486f72', '#385a5d', '#2f494c', '#283d40', '#243437', '#131e20']);
    const palette = {
        emerald: forest, green: forest, lime: forest, clcst: forest,
        slate: stone, gray: stone, zinc: stone, neutral: stone, stone,
        amber: brass, yellow: brass, orange: brass,
        red: oxblood, rose: oxblood,
        blue: navy, indigo: navy, sky: petrol, cyan: petrol, teal: petrol,
        violet: plum, purple: plum, fuchsia: plum,
        primary: '#1f3c27', 'primary-container': '#284a30', 'primary-fixed': '#e1e8df', 'on-primary': '#fffdf9',
        secondary: '#8b6b31', 'secondary-container': '#efe4c8', 'on-secondary-container': '#46361d', 'secondary-fixed': '#f3e8cf',
        tertiary: '#2f3f58', 'tertiary-fixed': '#dfe5ee',
        background: '#f6f3ec', surface: '#f6f3ec', 'surface-container-lowest': '#fffdf9', 'surface-container-low': '#f8f5ef',
        'surface-container': '#f0ece4', 'surface-container-high': '#e8e2d6', 'on-surface': '#1f1d1a', 'on-surface-variant': '#5e584d',
        outline: '#8a8275', 'outline-variant': '#ddd6c9', error: '#85362a', 'error-container': '#f3dfdb',
    };
    const serif = ['"Cormorant Garamond"', 'Georgia', 'serif'];
    const sans = ['Inter', 'system-ui', 'sans-serif'];
    const config = window.tailwind.config || {};
    config.theme = config.theme || {};
    const extend = config.theme.extend = config.theme.extend || {};
    extend.colors = Object.assign({}, extend.colors || {}, palette);
    extend.fontFamily = Object.assign({}, extend.fontFamily || {}, {
        sans, body: sans, 'body-md': sans, 'label-sm': sans, 'label-md': sans,
        display: serif, headline: serif, 'headline-sm': serif, 'headline-md': serif, 'headline-lg': serif, serif,
    });
    window.tailwind.config = config;
})();
</script>
