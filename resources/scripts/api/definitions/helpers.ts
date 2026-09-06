import {
    FractalPaginatedResponse,
    FractalResponseData,
    FractalResponseList,
    getPaginationSet,
    PaginatedResult,
} from '@/api/http';

type TransformerFunc<T = any> = (callback: FractalResponseData) => T;

const isList = (data: FractalResponseList | FractalResponseData): data is FractalResponseList => data.object === 'list';

function transform<T, M>(data: null | undefined, transformer: TransformerFunc<T>, missing?: M): M;
function transform<T, M>(
    data: FractalResponseData | null | undefined,
    transformer: TransformerFunc<T>,
    missing?: M,
): T | M;
function transform<T, M>(
    data: FractalResponseList | FractalPaginatedResponse | null | undefined,
    transformer: TransformerFunc<T>,
    missing?: M,
): T[] | M;
function transform<T>(
    data: FractalResponseData | FractalResponseList | FractalPaginatedResponse | null | undefined,
    transformer: TransformerFunc<T>,
    missing = undefined,
) {
    if (data === undefined || data === null) {
        return missing;
    }

    if (isList(data)) {
        return data.data.map(transformer);
    }

    if (!data || !data.attributes || data.object === 'null_resource') {
        return missing;
    }

    return transformer(data);
}

function toPaginatedSet<T>(response: FractalPaginatedResponse, transformer: TransformerFunc<T>): PaginatedResult<T> {
    const items = transform(response, transformer) as T[];
    return {
        items,
        pagination: getPaginationSet(response.meta.pagination),
    };
}

export { transform, toPaginatedSet };
