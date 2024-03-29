<template>
  <DefaultField
    :field="currentField"
    :errors="errors"
    :full-width-content="true"
    :show-help-text="showHelpText"
  >
    <template #field>
      <div
        class="flex flex-wrap mb-2 bg-white rounded-lg dark:bg-gray-800"
        :class="`nova-eloquent-imagery-${resourceName}`"
      >
        <template v-if="currentField.isCollection">
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
                  :editable="!currentlyIsReadonly"
                  :metadata="element.metadata"
                  :metadata-form-configuration="currentField.metadataFormConfiguration"
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
            :editable="!currentlyIsReadonly"
            :metadata="singleImage.metadata"
            :metadata-form-configuration="currentField.metadataFormConfiguration"
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
            :id="`eloquent-imagery-` + currentField.name + `-add-image`"
            ref="uploadNewImageFromFileInput"
            class="select-none form-file-input"
            type="file"
            name="name"
            @change.prevent="handleNewImageFromFileInput($event.target.files[0])"
          >

          <span
            v-if="!currentlyIsReadonly"
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
import { DependentFormField, HandlesValidationErrors } from 'laravel-nova'
import Draggable from 'vuedraggable'
import ImageCard from './ImageCard'

import createImageCollectionStore from './createImageCollectionStore'
import { ulid } from 'ulid'

export default {
  components: {
    Draggable,
    ImageCard
  },

  mixins: [
    DependentFormField,
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
    if (this.currentField.isCollection) {
      this.$store.registerModule(`eloquentImagery/${this.currentField.attribute}`, createImageCollectionStore())

      const requiredMetadataFields = []

      this.currentField.metadataFormConfiguration.fields.forEach(field => {
        if (field.required) {
          requiredMetadataFields.push(field.key)
        }
      })

      this.$store.commit(`eloquentImagery/${this.currentField.attribute}/initialize`, {
        isReadOnly: false,
        fieldName: this.currentField.attribute,
        images: this.currentField.value.images,
        requiredMetadataFields
      })

      this.imageCollection = this.$store.getters[`eloquentImagery/${this.currentField.attribute}/getImages`]
    } else {
      this.singleImage = this.field.value
    }
  },

  beforeUnmount () {
    if (this.currentField.isCollection) {
      this.$store.unregisterModule(`eloquentImagery/${this.currentField.attribute}`)
    }
  },

  methods: {
    fill (formData) {
      const value = (this.currentField.isCollection)
        ? this.$store.getters[`eloquentImagery/${this.currentField.attribute}/serialize`]
        : this.singleImage

      formData.append(this.currentField.attribute, value !== null ? JSON.stringify(value) : '')
    },

    handleImageCollectionRemoveImage (image) {
      this.$store.dispatch(`eloquentImagery/${this.currentField.attribute}/removeImage`, image)

      this.imageCollection = this.$store.getters[`eloquentImagery/${this.currentField.attribute}/getImages`]
    },

    handleImageCollectionReplaceImage (image, file) {
      this.$store.dispatch(`eloquentImagery/${this.currentField.attribute}/replaceImageWithFile`, { id: image.id, file })
    },

    handleImageCollectionUpdateMetadataForImage (image, metadatas) {
      this.$store.dispatch(
        `eloquentImagery/${this.currentField.attribute}/updateImageMetadata`,
        { id: image.id, metadatas, replace: true }
      )
    },

    handleImageCollectionUpdateOrder (dragEvent) {
      this.$store.dispatch(
        `eloquentImagery/${this.currentField.attribute}/updateOrder`,
        { oldIndex: dragEvent.oldIndex, newIndex: dragEvent.newIndex }
      )
    },

    handleReplaceSingleImage (file) {
      const imageUrl = URL.createObjectURL(file)

      this.singleImage.id = ulid().toLowerCase()
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
      this.singleImage.metadata = metadata.reduce(
        (o, m) => Object.assign(o, { [m.key]: m.value }), {}
      )
    },

    handleNewImageFromFileInput (file) {
      if (this.currentField.isCollection) {
        this.$store.dispatch(`eloquentImagery/${this.currentField.attribute}/addImageFromFile`, { file })
          .then(() => {
            this.imageCollection = this.$store.getters[`eloquentImagery/${this.currentField.attribute}/getImages`]
          })
      } else {
        const imageUrl = URL.createObjectURL(file)

        this.singleImage = {
          id: ulid().toLowerCase(),
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
