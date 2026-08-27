const normalizePath = (url) => {
    if (!url) {
        return '/';
    }

    const parsed = new URL(url, 'http://localhost');
    const normalized = parsed.pathname.replace(/\/+$/, '');

    return normalized || '/';
};

const resolveRouteUrl = (route) => {
    if (typeof route === 'string') {
        return route;
    }

    return route?.url;
};

const isCurrentRoute = (currentUrl, route) => {
    return normalizePath(currentUrl) === normalizePath(resolveRouteUrl(route));
};

export { isCurrentRoute, normalizePath, resolveRouteUrl };