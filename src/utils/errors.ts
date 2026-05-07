export const apiError = (message: string, status = 400) =>
  new Response(JSON.stringify({ error: message }), { status, headers: { 'content-type': 'application/json' } });

export const handleApiError = (error: unknown) => {
  if (error instanceof Response) return error;
  if (error instanceof Error) return apiError(error.message, 400);
  return apiError('Internal Server Error', 500);
};
