/**
 * Custom Tiptap extension for inserting tooth number chips.
 * Renders inline badge-styled chips like [#14] that are clickable.
 */
import { Node, mergeAttributes } from '@tiptap/core';

export const ToothChipExtension = Node.create({
  name: 'toothChip',
  group: 'inline',
  inline: true,
  atom: true,

  addAttributes() {
    return {
      toothNumber: { default: null, parseHTML: el => el.getAttribute('data-tooth') },
    };
  },

  parseHTML() {
    return [{ tag: 'span[data-tooth]' }];
  },

  renderHTML({ node, HTMLAttributes }) {
    return ['span', mergeAttributes(HTMLAttributes, {
      'data-tooth': node.attrs.toothNumber,
      class: 'tooth-chip',
      contenteditable: 'false',
    }), `#${node.attrs.toothNumber}`];
  },

  addCommands() {
    return {
      insertToothChip: (toothNumber) => ({ commands }) => {
        return commands.insertContent({
          type: this.name,
          attrs: { toothNumber },
        });
      },
    };
  },
});

export default ToothChipExtension;
