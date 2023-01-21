import IndexField from './nova/IndexField'
import DetailField from './nova/DetailField'
import FormField from './nova/FormField'

Nova.booting((app, store) => {
  /* eslint-disable vue/component-definition-name-casing */
  /* Nova expects names in this format */
  app.component('index-eloquent-imagery', IndexField)
  app.component('detail-eloquent-imagery', DetailField)
  app.component('form-eloquent-imagery', FormField)
  /* eslint-enable */
})
