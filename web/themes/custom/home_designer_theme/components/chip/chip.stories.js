export default {
  title: 'Components/chip',
};

// Twig file.
import myTemplate from "./chip.twig";

// CSS file.
import './chip.css';

// JS file.
import './chip.js';

export const chip = () => (
  myTemplate({
    title: 'Lorem ipsum!',
  })
);