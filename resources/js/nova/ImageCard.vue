<template>
  <div class="px-2 py-2">
    <img
      v-if="thumbnailUrl"
      alt="An eloquent imagery image"
      style="max-height: 100px"
      class="block mx-auto mb-2 sm:mb-0 sm:mr-4 sm:ml-0"
      :src="thumbnailUrl"
      @click.prevent="isPreviewImageModalOpen = true"
    >

    <Modal
      data-testid="image-card-modal"
      :show="isPreviewImageModalOpen"
      modal-style="window"
      size="4xl"
      @close-via-escape="handleClose"
      class="bg-gray-100 dark:bg-gray-700 px-6"
    >
      <ModalHeader></ModalHeader>
      <img
        alt="An eloquent imagery image"
        class="block mx-auto mb-4 sm:mb-0 sm:mr-4 sm:ml-0"
        :src="previewUrl"
      >
      <ModalFooter>
        <div class="flex items-center ml-auto">
          <CancelButton
            component="button"
            type="button"
            dusk="cancel-action-button"
            class="ml-auto mr-3"
            @click="handleClose"
          >
            Close
          </CancelButton>
        </div>
      </ModalFooter>
    </Modal>

    <input
      v-if="editable"
      ref="replaceImageFileInput"
      class="select-none form-file-input"
      type="file"
      @change="handleReplaceImage"
    >

    <div class="flex">
      <div
        v-if="editable"
        class="flex-1 text-center cursor-pointer"
        @click="$refs['replaceImageFileInput'].click()"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          width="24"
          height="24"
          fill="currentColor"
        >
          <path d="M13 5.41V17a1 1 0 0 1-2 0V5.41l-3.3 3.3a1 1 0 0 1-1.4-1.42l5-5a1 1 0 0 1 1.4 0l5 5a1 1 0 1 1-1.4 1.42L13 5.4zM3 17a1 1 0 0 1 2 0v3h14v-3a1 1 0 0 1 2 0v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3z" />
        </svg>
      </div>
      <div
        class="flex-1 text-center cursor-pointer"
        @click.prevent="handleOpenMetadataModal"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          width="24"
          height="24"
          fill="currentColor"
        >
          <path d="M2.59 13.41A1.98 1.98 0 0 1 2 12V7a5 5 0 0 1 5-5h4.99c.53 0 1.04.2 1.42.59l8 8a2 2 0 0 1 0 2.82l-8 8a2 2 0 0 1-2.82 0l-8-8zM20 12l-8-8H7a3 3 0 0 0-3 3v5l8 8 8-8zM7 8a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
        </svg>
      </div>
      <div
        v-if="editable"
        class="flex-1 text-center cursor-pointer"
        @click.prevent="$emit('removeImage')"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          width="24"
          height="24"
          fill="currentColor"
        >
          <path d="M16.24 14.83a1 1 0 0 1-1.41 1.41L12 13.41l-2.83 2.83a1 1 0 0 1-1.41-1.41L10.59 12 7.76 9.17a1 1 0 0 1 1.41-1.41L12 10.59l2.83-2.83a1 1 0 0 1 1.41 1.41L13.41 12l2.83 2.83z" />
        </svg>
      </div>
    </div>

    <div
      v-if="metadataMessaging"
      class="mt-1 text-danger-dark text-xs"
    >
      {{ metadataMessaging }}
    </div>

    <Modal
      data-testid="image-card-modal"
      :show="isMetadataModalOpen"
      modal-style="window"
      size="4xl"
      @close-via-escape="handleClose"
    >
      <div class="w-screen">
        <div
          class="m-auto bg-white select-text dark:bg-gray-800"
          style="min-height: 12em"
        >
          <div class="w-full p-8 m-2">
            <h3 class="mb-2">
              Image Metadata
            </h3>

            <div
              v-for="(metadataField, index) in metadataForm.fields"
              :key="index"
              class="w-full flex items-center"
            >
              <div class="pr-2 text-right font-bold" style="width: 33%">
                <template v-if="metadataField.isKeyEditable">
                  <input
                    v-if="editable"
                    v-model="metadataField.key"
                    type="text"
                    class="w-full text-xs form-control form-input form-input-bordered m-1"
                  >
                  <span
                    v-else
                    class="w-full text-xs form-control form-input form-input-bordered m-1"
                  >
                    {{ metadataField.key }}
                  </span>
                </template>
                <template v-else>
                  {{ metadataField.label }}
                </template>
                <span
                  v-if="metadataField.isRequired"
                  class="text-danger-dark"
                >
                  *
                </span>
              </div>
              <div class="w-full">
                <input
                  v-if="editable"
                  v-model="metadataForm.fields[index].value"
                  class="w-full text-xs form-control form-input form-input-bordered my-1"
                  required
                  type="text"
                >
                <span
                  v-else
                  class="w-full text-xs form-control form-input form-input-bordered my-1"
                >
                  {{ metadataForm.fields[index].value }}
                </span>
              </div>
              <div>
                <span
                  v-if="editable && metadataField.isRemovable"
                  class="cursor-pointer my-2 ml-2"
                  @click.prevent="metadataForm.fields.splice(index, 1)"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    width="24"
                    height="24"
                    fill="currentColor"
                  >
                    <path d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2zm0 2v14h14V5H5zm11 7a1 1 0 0 1-1 1H9a1 1 0 0 1 0-2h6a1 1 0 0 1 1 1z" />
                  </svg>
                </span>
              </div>
            </div>
          </div>

          <button
            v-if="editable && metadataForm.allowAddMetadata"
            class="flex items-center mx-auto mt-2"
            @click.prevent="handleAddMetadata"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              width="24"
              height="24"
              fill="currentColor"
            >
              <path d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2zm0 2v14h14V5H5zm8 6h2a1 1 0 0 1 0 2h-2v2a1 1 0 0 1-2 0v-2H9a1 1 0 0 1 0-2h2V9a1 1 0 0 1 2 0v2z"/>
            </svg>
            <span class="ml-2">
              Add Metadata
            </span>
          </button>

          <div class="text-right mt-2 p-4">
            <button
              class="btn btn-link dim cursor-pointer text-80 ml-auto mr-6"
              @click.prevent="handleCloseMetadataModal"
            >
              {{ editable ? 'Cancel' : 'Close' }}
            </button>
            <button
              v-if="editable"
              class="btn btn-default btn-primary inline-flex items-center relative"
              @click.prevent="handleUpdateAndCloseMetadataModal"
            >
              Update &amp; Close
            </button>
          </div>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
export default {
  props: {
    editable: {
      type: Boolean,
      default: false
    },
    metadata: {
      type: Object,
      required: true
    },
    metadataFormConfiguration: {
      type: Object,
      required: true
    },
    previewUrl: {
      type: String,
      required: true
    },
    thumbnailUrl: {
      type: String,
      required: true
    }
  },

  data () {
    return {
      isMetadataModalOpen: false,
      isPreviewImageModalOpen: false,
      metadataForm: {
        allowAddMetadata: false,
        fields: [],
        requiredFields: []
      }
    }
  },

  computed: {
    metadataMessaging () {
      for (const field of this.metadataForm.requiredFields) {
        if (!this.metadata[field] || this.metadata[field] === '') {
          return 'Required metadata is missing.'
        }
      }

      return null
    }
  },

  created () {
    this.refreshMetadataForm()
  },

  methods: {
    handleReplaceImage (event) {
      this.$emit('replaceImage', event.target.files[0])
    },

    handleOpenMetadataModal (event) {
      this.isMetadataModalOpen = true
      this.refreshMetadataForm()
    },

    handleUpdateAndCloseMetadataModal () {
      const metadatas = []

      this.metadataForm.fields.forEach(field => {
        metadatas.push({ key: field.key, value: field.value })
      })

      this.$emit('updateMetadata', metadatas)
      this.isMetadataModalOpen = false
    },

    handleAddMetadata () {
      this.metadataForm.fields.push({
        key: '',
        label: '',
        value: '',
        isKeyEditable: true,
        isHidden: false,
        isRequired: false,
        isRemovable: true
      })
    },

    handleCloseMetadataModal () {
      this.isMetadataModalOpen = false
      this.refreshMetadataForm()
    },

    handleClose () {
      this.isMetadataModalOpen = false
      this.isPreviewImageModalOpen = false
    },

    refreshMetadataForm () {
      // configuration of the form
      this.metadataForm.allowAddMetadata = this.metadataFormConfiguration.allowAddMetadata ?? false

      this.metadataForm.fields = []

      this.metadataFormConfiguration.fields.forEach(field => {
        if (field.required) {
          this.metadataForm.requiredFields.push(field.key)
        }

        this.metadataForm.fields.push({
          key: field.key,
          label: field.label ?? field.key,
          value: null,
          isKeyEditable: false,
          isHidden: false,
          isRequired: field.required ?? false,
          isRemovable: field.removable ?? true
        })
      })

      Object.entries(this.metadata).forEach(([key, value]) => {
        const foundFieldIndex = this.metadataForm.fields.findIndex(field => field.key === key)

        if (foundFieldIndex !== -1) {
          this.metadataForm.fields[foundFieldIndex].value = value

          return
        }

        this.metadataForm.fields.push({
          key,
          label: key,
          value: value,
          isKeyEditable: true,
          isHidden: false,
          isRequired: false,
          isRemovable: true
        })
      })
    }
  }
}
</script>
