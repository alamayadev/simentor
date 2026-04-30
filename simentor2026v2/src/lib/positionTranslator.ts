/**
 * Translates DOM element coordinates into human-readable Indonesian descriptions.
 * Uses a 3x3 grid to determine the position relative to the viewport.
 */
export function translatePosition(rect: DOMRect): string {
  const windowWidth = window.innerWidth;
  const windowHeight = window.innerHeight;

  const centerX = rect.left + rect.width / 2;
  const centerY = rect.top + rect.height / 2;

  let horizontal = "";
  if (centerX < windowWidth / 3) {
    horizontal = "kiri";
  } else if (centerX < (2 * windowWidth) / 3) {
    horizontal = "tengah";
  } else {
    horizontal = "kanan";
  }

  let vertical = "";
  if (centerY < windowHeight / 3) {
    vertical = "atas";
  } else if (centerY < (2 * windowHeight) / 3) {
    vertical = "tengah";
  } else {
    vertical = "bawah";
  }

  // Special case for center of screen
  if (horizontal === "tengah" && vertical === "tengah") {
    return "di tengah layar";
  }

  return `di ${horizontal} ${vertical} halaman`;
}
