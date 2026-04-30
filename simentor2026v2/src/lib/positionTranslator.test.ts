import { beforeEach, describe, expect, it, vi } from 'vitest';
import { translatePosition } from './positionTranslator';

describe('translatePosition', () => {
  beforeEach(() => {
    vi.stubGlobal('innerWidth', 900);
    vi.stubGlobal('innerHeight', 600);
  });

  function makeRect(left: number, top: number, width = 50, height = 50): DOMRect {
    return {
      left,
      top,
      width,
      height,
      right: left + width,
      bottom: top + height,
      x: left,
      y: top,
      toJSON: () => {},
    };
  }

  it('returns center description for center of viewport', () => {
    const rect = makeRect(425, 275);
    expect(translatePosition(rect)).toBe('di tengah layar');
  });

  it('returns top-left position', () => {
    const rect = makeRect(10, 10);
    expect(translatePosition(rect)).toBe('di kiri atas halaman');
  });

  it('returns bottom-right position', () => {
    const rect = makeRect(850, 550);
    expect(translatePosition(rect)).toBe('di kanan bawah halaman');
  });

  it('returns top-center position', () => {
    const rect = makeRect(425, 10);
    expect(translatePosition(rect)).toBe('di tengah atas halaman');
  });

  it('returns bottom-left position', () => {
    const rect = makeRect(10, 550);
    expect(translatePosition(rect)).toBe('di kiri bawah halaman');
  });
});
