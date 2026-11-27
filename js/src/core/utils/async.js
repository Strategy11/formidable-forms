// Initialize lastPromise with a resolved promise as the starting point for the queue
let lastPromise = Promise.resolve();

/**
 * Adds a task to the request queue.
 *
 * @param {function(): Promise<any>} task A function that returns a promise.
 * @return {Promise<any>} The new last promise in the queue.
 */
export const addToRequestQueue = task => lastPromise = lastPromise.then( task ).catch( task );
