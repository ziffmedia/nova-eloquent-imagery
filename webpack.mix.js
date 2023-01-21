const mix = require('laravel-mix')

require('./nova.mix')

mix
  .setPublicPath('dist')
  .js('resources/js/nova.js', 'js')
  .vue({ version: 3 })
  .nova('nova-eloquent-imagery')
  .options({ terser: { extractComments: false } })

if (!mix.inProduction()) {
  mix.sourceMaps()
}
