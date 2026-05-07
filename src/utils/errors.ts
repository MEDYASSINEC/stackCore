export const apiError = (message: string, status = 400) =>
  new Response(JSON.stringify({ error: message }), { status, headers: { 'content-type': 'application/json' } });
