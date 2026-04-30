import { useState, useCallback, useMemo } from 'react';

interface PaginationMeta {
  total: number;
  lastPage: number;
  from: number;
  to: number;
}

/**
 * Robust hook for managing pagination state in list views.
 * Supports both offset-based (page/per_page) and cursor-based pagination.
 * Handles metadata normalization and smart parameter generation.
 */
export function usePagination(defaultPerPage = 15) {
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(defaultPerPage);
  const [cursor, setCursor] = useState<string | null>(null);
  const [cursorHistory, setCursorHistory] = useState<string[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | null>(null);

  /**
   * Helper to sync pagination metadata from various API response structures.
   * Standardizes nested 'meta' and 'pagination_info' blocks.
   */
  const sync = useCallback((response: any) => {
    const dataObj = response as any;
    const m = dataObj?.pagination_info || dataObj?.meta || dataObj?.data?.meta;
    
    if (m) {
      setMeta({
        total: m.total_records || m.total || response?.data?.length || 0,
        lastPage: m.total_page || m.last_page || 1,
        from: m.from || 0,
        to: m.to || 0,
      });
    }
  }, []);

  /**
   * Dynamically generates the parameters needed for the API request.
   * Automatically switches between 'page' and 'cursor' based on current state.
   */
  const params = useMemo(() => {
    const p: Record<string, any> = {};
    
    // Only include per_page if NOT on the first page/request
    // This allows the server to use its default value for the initial load.
    if (page > 1 || cursor) {
      p.per_page = perPage;
    }
    
    if (cursor) {
      // Check if the cursor is actually a page number (Hybrid behavior)
      if (cursor.includes('page=') || /^[0-9]+$/.test(cursor)) {
        const pageVal = cursor.includes('page=') 
          ? new URLSearchParams(cursor.split('?')[1]).get('page') 
          : cursor;
        if (pageVal) p.page = pageVal;
      } else {
        p.cursor = cursor;
      }
    } else if (page > 1) {
      p.page = page;
    }
    
    return p;
  }, [page, perPage, cursor]);

  /**
   * Robustly extracts the next token (cursor or page) from a backend provided link.
   */
  const handleNext = useCallback((nextRaw?: string | null) => {
    // If no next token/URL is provided, assume simple page increment
    if (!nextRaw) {
      setCursorHistory(prev => [...prev, cursor || String(page)]);
      setPage(prev => prev + 1);
      return;
    }
    
    // Save current position to history for 'Prev' support
    setCursorHistory(prev => [...prev, cursor || String(page)]);
    
    let finalToken = nextRaw;
    if (nextRaw.includes('?')) {
      const searchParams = new URLSearchParams(nextRaw.split('?')[1]);
      finalToken = searchParams.get('cursor') || searchParams.get('page') || nextRaw;
    }
    
    setCursor(finalToken);
    setPage(prev => prev + 1);
  }, [cursor, page]);

  /**
   * Handles going back, managing cursor history for non-offset APIs.
   */
  const handlePrev = useCallback(() => {
    if (cursorHistory.length === 0) {
      setPage(1);
      setCursor(null);
      return;
    }

    const history = [...cursorHistory];
    const prevToken = history.pop() || null;
    setCursorHistory(history);
    
    // If it's a simple page number, just update 'page'
    if (prevToken && (/^[0-9]+$/.test(prevToken) || prevToken.includes('page='))) {
      const pageVal = prevToken.includes('page=') 
        ? new URLSearchParams(prevToken.split('?')[1]).get('page') 
        : prevToken;
      setPage(Number(pageVal) || 1);
      setCursor(null);
    } else {
      setCursor(prevToken);
      setPage(prev => Math.max(1, prev - 1));
    }
  }, [cursorHistory]);

  const reset = useCallback(() => {
    setPage(1);
    setCursor(null);
    setCursorHistory([]);
  }, []);

  return {
    page,
    setPage,
    perPage,
    setPerPage,
    cursor,
    setCursor,
    meta,
    setMeta,
    params,
    sync,
    handleNext,
    handlePrev,
    reset,
    canNext: meta ? page < meta.lastPage : !!cursor,
    canPrev: page > 1 || cursorHistory.length > 0,
    totalPages: meta?.lastPage || 1,
    totalRecords: meta?.total || 0,
    canGoNext: meta ? page < meta.lastPage : !!cursor,
    canGoPrev: page > 1 || cursorHistory.length > 0,
  };
}

