import React from 'react';

interface Md2ReactProps {
    markdown: string;
}

const parseBold = (text: string): (string | React.ReactElement)[] => {
    const boldRegex = /\*\*(.*?)\*\*/g;
    const result: (string | React.ReactElement)[] = [];
    let lastIndex = 0;
    let match;

    while ((match = boldRegex.exec(text)) !== null) {
        if (match.index > lastIndex) {
            result.push(text.slice(lastIndex, match.index));
        }
        result.push(<strong key={match.index}>{match[1]}</strong>);
        lastIndex = boldRegex.lastIndex;
    }

    if (lastIndex < text.length) {
        result.push(text.slice(lastIndex));
    }

    return result;
};

const Md2React = ({ markdown }: Md2ReactProps) => {
    const linkRegex = /\[([^\]]+)\]\(([^)]+)\)/g;
    const parts: (string | React.ReactElement)[] = [];
    let lastIndex = 0;
    let match;

    while ((match = linkRegex.exec(markdown)) !== null) {
        const textBefore = markdown.substring(lastIndex, match.index);
        parts.push(...parseBold(textBefore));

        parts.push(
            <a
                href={match[2]}
                key={`link-${match.index}`}
                className='font-semibold'
                target='_blank'
                rel='noopener noreferrer'
            >
                {match[1]}
            </a>,
        );
        lastIndex = match.index + match[0].length;
    }

    const textAfter = markdown.substring(lastIndex);
    if (textAfter) {
        parts.push(...parseBold(textAfter));
    }

    return <>{parts}</>;
};

export default Md2React;
