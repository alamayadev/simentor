import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { renderHook, act } from '@testing-library/react';
import { useDebounce } from './useDebounce';

describe('useDebounce', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('returns initial value immediately', () => {
    const { result } = renderHook(() => useDebounce('hello', 500));
    expect(result.current).toBe('hello');
  });

  it('updates to debounced value after delay', () => {
    const { result, rerender } = renderHook(
      ({ value, delay }) => useDebounce(value, delay),
      { initialProps: { value: 'hello', delay: 300 } },
    );

    expect(result.current).toBe('hello');

    rerender({ value: 'world', delay: 300 });
    expect(result.current).toBe('hello');

    act(() => {
      vi.advanceTimersByTime(300);
    });
    expect(result.current).toBe('world');
  });

  it('resets timer when value changes rapidly', () => {
    const { result, rerender } = renderHook(
      ({ value, delay }) => useDebounce(value, delay),
      { initialProps: { value: 'a', delay: 300 } },
    );

    rerender({ value: 'b', delay: 300 });
    act(() => {
      vi.advanceTimersByTime(100);
    });
    expect(result.current).toBe('a');

    rerender({ value: 'c', delay: 300 });
    act(() => {
      vi.advanceTimersByTime(100);
    });
    expect(result.current).toBe('a');

    act(() => {
      vi.advanceTimersByTime(300);
    });
    expect(result.current).toBe('c');
  });
});
