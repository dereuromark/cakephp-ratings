import { defineConfig } from 'vitepress'

const sidebar = [
  { text: 'Guide', items: [
    { text: 'Installation', link: '/guide/installation' },
    { text: 'Getting started', link: '/guide/getting-started' },
    { text: 'Behavior', link: '/guide/behavior' },
    { text: 'Component', link: '/guide/component' },
    { text: 'Helper and assets', link: '/guide/helper' },
  ] },
  { text: 'Reference', items: [
    { text: 'Configuration', link: '/reference/configuration' },
    { text: 'Upgrading', link: '/reference/upgrading' },
  ] },
]

export default defineConfig({
  title: 'CakePHP Ratings',
  description: 'Ratings and votes for CakePHP records.',
  base: '/cakephp-ratings/',
  cleanUrls: true,
  lastUpdated: true,
  srcExclude: ['README.md', 'Documentation/**', 'Tutorials/**'],
  sitemap: { hostname: 'https://dereuromark.github.io/cakephp-ratings/' },
  head: [['link', { rel: 'icon', href: '/cakephp-ratings/favicon.svg', type: 'image/svg+xml' }]],
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Installation', link: '/guide/installation' },
      { text: 'Getting started', link: '/guide/getting-started' },
      { text: 'Reference', link: '/reference/configuration' },
    ],
    sidebar,
    socialLinks: [{ icon: 'github', link: 'https://github.com/dereuromark/cakephp-ratings' }],
    search: { provider: 'local' },
    editLink: { pattern: 'https://github.com/dereuromark/cakephp-ratings/edit/master/docs/:path' },
    footer: { message: 'Released under the MIT License.', copyright: 'Copyright Mark Scherer' },
  },
})
