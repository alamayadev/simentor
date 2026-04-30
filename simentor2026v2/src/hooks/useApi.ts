import { useQuery, useMutation, useQueryClient, type UseQueryOptions } from '@tanstack/react-query';
import { ApiError, type ApiResponse } from '../lib/api';

/**
 * Generic query hook for GET API calls.
 * Wraps @tanstack/react-query useQuery with our API response shape.
 */
export function useApiQuery<T>(
  queryKey: unknown[],
  queryFn: () => Promise<ApiResponse<T>>,
  options?: Omit<UseQueryOptions<ApiResponse<T>, ApiError>, 'queryKey' | 'queryFn'>
) {
  return useQuery<ApiResponse<T>, ApiError>({
    queryKey,
    queryFn,
    ...options,
  });
}

/**
 * Generic mutation hook for POST/PUT/PATCH/DELETE API calls.
 * Automatically invalidates specified query keys on success.
 */
export function useApiMutation<TData, TVariables>(
  mutationFn: (vars: TVariables) => Promise<ApiResponse<TData>>,
  options?: {
    invalidateKeys?: unknown[][];
    onSuccess?: (data: ApiResponse<TData>, vars: TVariables) => void;
    onError?: (error: ApiError, vars: TVariables) => void;
  }
) {
  const queryClient = useQueryClient();

  return useMutation<ApiResponse<TData>, ApiError, TVariables>({
    mutationFn,
    onSuccess: (data, vars) => {
      if (options?.invalidateKeys) {
        options.invalidateKeys.forEach((key) => {
          queryClient.invalidateQueries({ queryKey: key });
        });
      }
      options?.onSuccess?.(data, vars);
    },
    onError: (error, vars) => {
      options?.onError?.(error, vars);
    },
  });
}
