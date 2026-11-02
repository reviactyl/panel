import Markdown from 'react-markdown';

export default function AlertMarkdown({ message }: { message: string }) {
    return (
        <div className='space-y-2 [&_blockquote]:border-l-2 [&_blockquote]:pl-3 [&_code]:rounded [&_code]:bg-black/20 [&_code]:px-1 [&_code]:font-mono [&_h1]:text-xl [&_h1]:font-bold [&_h2]:text-lg [&_h2]:font-bold [&_h3]:font-bold [&_ol]:list-decimal [&_ol]:pl-5 [&_pre]:overflow-x-auto [&_pre]:rounded [&_pre]:bg-black/20 [&_pre]:p-3 [&_ul]:list-disc [&_ul]:pl-5'>
            <Markdown
                skipHtml
                disallowedElements={['img']}
                components={{
                    a: ({ children, href }) => (
                        <a href={href} className='font-semibold underline' target='_blank' rel='noopener noreferrer'>
                            {children}
                        </a>
                    ),
                }}
            >
                {message}
            </Markdown>
        </div>
    );
}
