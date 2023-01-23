<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :full-width-content="true"
    :show-help-text="showHelpText"
  >
    <template #field>
      <div
        class="flex flex-wrap mb-2 bg-white rounded-lg dark:bg-gray-800"
        :class="`nova-eloquent-imagery-${resourceName}`"
      >
        <template v-if="field.isCollection">
          <draggable
            v-model="imageCollection"
            item-key="id"
            class="flex flex-wrap mb-2"
            group="image-group"
            @update="handleImageCollectionUpdateOrder"
          >
            <template #item="{element}">
              <div :class="`border border-70 flex items-end m-1 nova-eloquent-imagery-image-${ element.id }`">
                <image-card
                  :editable="true"
                  :metadata="element.metadata"
                  :metadata-form-configuration="field.metadataFormConfiguration"
                  :preview-url="element.previewUrl"
                  :thumbnail-url="element.thumbnailUrl"
                  @removeImage="handleImageCollectionRemoveImage(element)"
                  @replaceImage="handleImageCollectionReplaceImage(element, $event)"
                  @updateMetadata="handleImageCollectionUpdateMetadataForImage(element, $event)"
                />
              </div>
            </template>
          </draggable>
        </template>
        <template v-else>
          <image-card
            v-if="singleImage"
            :editable="true"
            :metadata="singleImage.metadata"
            :metadata-form-configuration="field.metadataFormConfiguration"
            :preview-url="singleImage.previewUrl"
            :thumbnail-url="singleImage.thumbnailUrl"
            @removeImage="handleRemoveSingleImage"
            @replaceImage="handleReplaceSingleImage"
            @updateMetadata="handleUpdateMetadataForSingleImage"
          />
        </template>

        <div
          v-if="imageCollection || (!imageCollection && !singleImage)"
          class="content-center px-6 py-4"
        >
          <input
            :id="`eloquent-imagery-` + field.name + `-add-image`"
            ref="uploadNewImageFromFileInput"
            class="select-none form-file-input"
            type="file"
            name="name"
            @change="handleNewImageFromFileInput($event.target.files[0])"
          >

          <span
            class="cursor-pointer"
            @click.prevent="$refs['uploadNewImageFromFileInput'].click()"
          >
            <!-- eslint-disable vue/max-attributes-per-line -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="72" width="72" fill="currentColor">
              <path fill="#888" d="M6 2h9a1 1 0 0 1 .7.3l4 4a1 1 0 0 1 .3.7v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4c0-1.1.9-2 2-2zm9 2.41V7h2.59L15 4.41zM18 9h-3a2 2 0 0 1-2-2V4H6v16h12V9zm-5 4h2a1 1 0 0 1 0 2h-2v2a1 1 0 0 1-2 0v-2H9a1 1 0 0 1 0-2h2v-2a1 1 0 0 1 2 0v2z"/>
            </svg>
            <!-- eslint-enable -->
          </span>
        </div>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import Draggable from 'vuedraggable'
import ImageCard from './ImageCard'

import createImageCollectionStore from './createImageCollectionStore'

export default {
  components: {
    Draggable,
    ImageCard
  },

  mixins: [
    FormField,
    HandlesValidationErrors
  ],

  props: {
    resourceName: {
      type: String,
      required: true
    },
    resourceId: {
      type: String,
      required: true
    },
    field: {
      type: Object,
      required: true
    }
  },

  data () {
    return {
      imageCollection: null,
      singleImage: null
    }
  },

  created () {
    if (this.field.isCollection) {
      this.$store.registerModule(`eloquentImagery/${this.field.attribute}`, createImageCollectionStore())

      const requiredMetadataFields = []

      this.field.metadataFormConfiguration.fields.forEach(field => {
        if (field.required) {
          requiredMetadataFields.push(field.key)
        }
      })

      this.$store.commit(`eloquentImagery/${this.field.attribute}/initialize`, {
        isReadOnly: false,
        fieldName: this.field.attribute,
        images: this.field.value.images,
        requiredMetadataFields
      })

      this.imageCollection = this.$store.getters[`eloquentImagery/${this.field.attribute}/getImages`]
    } else {
      this.singleImage = this.field.value
    }
  },

  unmounted () {
    if (this.field.isCollection) {
      this.$store.unregisterModule(`eloquentImagery/${this.field.attribute}`)
    }
  },

  methods: {
    fill (formData) {
      const value = (this.field.isCollection)
        ? this.$store.getters[`eloquentImagery/${this.field.attribute}/serialize`]
        : this.singleImage

      formData.append(this.field.attribute, JSON.stringify(value))
    },

    handleImageCollectionRemoveImage (image) {
      this.$store.dispatch(`eloquentImagery/${this.field.attribute}/removeImage`, image)

      this.imageCollection = this.$store.getters[`eloquentImagery/${this.field.attribute}/getImages`]
    },

    handleImageCollectionReplaceImage (image, file) {
      this.$store.dispatch(`eloquentImagery/${this.field.attribute}/replaceImageWithFile`, { id: image.id, file })
    },

    handleImageCollectionUpdateMetadataForImage (image, metadatas) {
      this.$store.dispatch(
        `eloquentImagery/${this.field.attribute}/updateImageMetadata`,
        { id: image.id, metadatas, replace: true }
      )
    },

    handleImageCollectionUpdateOrder (dragEvent) {
      debugger
      this.$store.dispatch(
        `eloquentImagery/${this.field.attribute}/updateOrder`,
        { oldIndex: dragEvent.oldIndex, newIndex: dragEvent.newIndex }
      )
    },

    handleReplaceSingleImage (file) {
      const imageUrl = URL.createObjectURL(file)

      this.singleImage.previewUrl = imageUrl
      this.singleImage.thumbnailUrl = imageUrl

      return new Promise((resolve, reject) => {
        const reader = new FileReader()

        reader.addEventListener('load', () => {
          this.singleImage.fileData = reader.result
        })

        reader.readAsDataURL(file)
      })
    },

    handleRemoveSingleImage (image) {
      this.singleImage = null
    },

    handleUpdateMetadataForSingleImage (metadata) {
      const newm = metadata.reduce((o, m) => Object.assign(o, { [m.key]: m.value }), {})
      this.singleImage.metadata = newm
    },

    handleNewImageFromFileInput (file) {
      if (this.field.isCollection) {
        this.$store.dispatch(`eloquentImagery/${this.field.attribute}/addImageFromFile`, { file })
          .then(() => {
            this.imageCollection = this.$store.getters[`eloquentImagery/${this.field.attribute}/getImages`]
          })
      } else {
        const imageUrl = URL.createObjectURL(file)

        this.singleImage = {
          id: this.field.attribute,
          previewUrl: imageUrl,
          thumbnailUrl: imageUrl
        }

        this.singleImage.metadata = {}

        return new Promise((resolve, reject) => {
          const reader = new FileReader()

          reader.addEventListener('load', () => {
            this.singleImage.fileData = reader.result
          })

          reader.readAsDataURL(file)
        })
      }
    }
  }
}
</script>
