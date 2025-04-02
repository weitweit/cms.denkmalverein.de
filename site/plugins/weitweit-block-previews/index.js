panel.plugin("weitweit-blocks-preview/preview", {
  blocks: {
    weitweit: {
      data() {
        return this.fieldset;
      },
      computed: {
        excerpt() {
          let excerpt = "";

          if (this.content.label) {
            excerpt += `<em>${getStringMaxLength(this.content.label)}</em><br>`;
          }
          if (this.content.headline) {
            excerpt += `<strong>${getStringMaxLength(
              this.content.headline
            )}</strong><br>`;
          }
          if (this.content.text) {
            excerpt += `${getStringMaxLength(this.content.text, 120)}<br>`;
          }
          if (this.content.caption) {
            excerpt += `${getStringMaxLength(this.content.caption, 120)}<br>`;
          }

          return excerpt;
        },
        anchor() {
          return getAnchor(this);
        },
        order() {
          if (!this.content.order) {
            return null;
          }

          if (this.content.order === "image-first") {
            return "Bild zuerst";
          }

          if (this.content.order === "text-first") {
            return "Text zuerst";
          }

          if (this.content.order === "right") {
            return "Rechts";
          }

          if (this.content.order === "full") {
            return "Volle Breite";
          }

          return "Links";
        },
        alignment() {
          if (!this.content.alignment) {
            return null;
          }

          if (this.content.alignment === "right") {
            return "Rechts";
          }

          if (this.content.alignment === "full") {
            return "Volle Breite";
          }

          return "Links";
        },
        size() {
          if (!this.content.size) {
            return null;
          }

          if (this.content.size === "full") {
            return "Full width";
          }
          return "Small / Portrait";
        },
        textsize() {
          if (!this.content.textsize) {
            return null;
          }

          if (this.content.textsize === "small") {
            return "Klein";
          }
          return "Groß";
        },
        images() {
          let contentImages = [];

          if (this.content.image) {
            contentImages.push(this.content.image);
          }

          if (this.content.image1) {
            contentImages.push(this.content.image1);
          }

          if (this.content.image2) {
            contentImages.push(this.content.image2);
          }

          if (this.content.imagesmall) {
            contentImages.push(this.content.imagesmall);
          }

          if (this.content.imagelarge) {
            contentImages.push(this.content.imagelarge);
          }

          let images = [];
          for (let i = 0; i < contentImages.length; i++) {
            let image = null;
            if (contentImages[i][0] !== undefined) {
              image = contentImages[i][0].image;
            } else if (contentImages[i].image !== undefined) {
              image = contentImages[i].image;
            }

            if (!image) {
              continue;
            }

            images[i] = {
              id: image.id,
              url: image.url,
              srcset: image.srcset,
              alt: image.alt,
            };
          }
          return images;
        },
        previewImagePath() {
          return `/assets/block-previews/${this.fieldset.type}.jpg`;
        },
      },
      template: getDefaultTemplate(),
    },
  },
});

function getStructureFirstColumn(data, key = "title", maxLength = 0) {
  let items = "";
  data.forEach((item) => {
    const value = item[key];

    if (!value) {
      return;
    }

    items += `<div>${getStringMaxLength(value, maxLength)}</div>`;
  });

  return items;
}

function getDefaultTemplate() {
  return `
    <div @dblclick="open">
      <template>
        <div class="k-grid">
          <div class="k-column" style="--width: 3/4">
            <div class="k-block-title">
              <k-icon :type="fieldset.icon" />
              <span>{{ fieldset.name }}</span>
            </div>
            <div class="k-column k-grid" style="--width: 3/4">
              <div class="k-column k-grid" style="--width: 1/1;">
                <div v-html="excerpt" class="k-column" style="--width: 4/5;"></div>
                <div class="k-column" style="--width: 2/5;" v-if="images">
                  <div class="k-grid" style="--columns: 4">
                    <div v-for="image in images" class="k-column">
                        <img
                          :src="image.url"
                          :srcset="image.srcset"
                          :alt="image.alt"
                        />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="k-column" style="--width: 1/4">
            <div v-html="anchor" class="anchor" v-if="anchor"></div>
            <div v-html="order" class="order" v-if="order"></div>
            <div v-html="alignment" class="alignment" v-if="alignment"></div>
            <div v-html="size" class="size" v-if="size"></div>
            <div v-html="textsize" class="textsize" v-if="textsize"></div>
          </div>
        </div>
      </template>
    </div>
  `;
}

function getStringMaxLength(string, max = 70) {
  if (string === "" || string === undefined) {
    return string;
  }

  if (string.length > max && max > 0) {
    return `${string.substring(0, max)}...`;
  }

  return `${string}`;
}

function getAnchor(data) {
  if (!data.content.anchor || data.content.anchor === "") {
    return null;
  }

  return `Anker (#): ${data.content.anchor}`;
}

function getMediaSize(data) {
  if (!data.content.mediasize || data.content.mediasize === "") {
    return null;
  }

  // map values to strings, e.g. full to Full, narrow to Narrow
  if (data.content.mediasize === "full") {
    return "Größe: Volle Breite";
  }

  if (data.content.mediasize === "narrow") {
    return "Größe: Schmal";
  }

  return `Größe: ${data.content.mediasize}`;
}

function getStyle(data) {
  if (!data.content.style || data.content.style === "") {
    return null;
  }

  return `Style: ${data.content.style}`;
}
