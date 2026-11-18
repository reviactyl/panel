import { useState } from 'react';
import { createRoot } from 'react-dom/client';
import Pagination from '@/reviactyl/elements/Pagination';
import PaginationFooter from '@/reviactyl/elements/table/PaginationFooter';
import '@/assets/tailwind.css';

const data = (currentPage: number) => ({ currentPage, totalPages: 101, perPage: 20, count: 20, total: 2020 });
function Fixture() {
    const [page, setPage] = useState(96);
    const [tablePage, setTablePage] = useState(2);
    return (
        <main className='p-4'>
            <section data-testid='numbered'>
                <Pagination data={{ items: [page], pagination: data(page) }} onPageSelect={setPage}>
                    {({ items }) => <p>Rows for page {items[0]}</p>}
                </Pagination>
            </section>
            <section data-testid='table'>
                <PaginationFooter pagination={data(tablePage)} onPageSelect={setTablePage} />
            </section>
        </main>
    );
}
createRoot(document.getElementById('root')!).render(<Fixture />);
