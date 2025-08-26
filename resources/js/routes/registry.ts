/**
 * Type-safe route helpers for Registry operations
 */

interface RouteParams {
    [key: string]: string | number;
}

interface RouteOptions {
    url: string;
    method: string;
}

class RegistryRoutes {
    private baseUrl = '/registry';

    /**
     * GET /registry - Show registry index
     */
    index(): RouteOptions {
        return {
            url: this.baseUrl,
            method: 'GET'
        };
    }

    /**
     * POST /registry - Store new registry entry
     */
    store(): RouteOptions {
        return {
            url: this.baseUrl,
            method: 'POST'
        };
    }

    /**
     * PUT /registry/{key} - Update registry entry
     */
    update(key: string): RouteOptions {
        return {
            url: `${this.baseUrl}/${encodeURIComponent(key)}`,
            method: 'PUT'
        };
    }

    /**
     * DELETE /registry/{key} - Delete registry entry
     */
    destroy(key: string): RouteOptions {
        return {
            url: `${this.baseUrl}/${encodeURIComponent(key)}`,
            method: 'DELETE'
        };
    }

    /**
     * PATCH /registry/{key}/rename - Rename registry entry
     */
    rename(key: string): RouteOptions {
        return {
            url: `${this.baseUrl}/${encodeURIComponent(key)}/rename`,
            method: 'PATCH'
        };
    }
}

const registryRoutes = new RegistryRoutes();

// Named exports with proper binding
export const index = () => registryRoutes.index();
export const store = () => registryRoutes.store();
export const update = (key: string) => registryRoutes.update(key);
export const destroy = (key: string) => registryRoutes.destroy(key);
export const rename = (key: string) => registryRoutes.rename(key);

export default registryRoutes;