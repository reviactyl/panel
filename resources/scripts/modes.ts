export interface Mode {
    name: string;
    mode: string; // Monaco language ID
    ext?: string[];
    alias?: string[];
    file?: RegExp;
}

const modes: Mode[] = [
    { name: 'C', mode: 'c', ext: ['c', 'h', 'ino'] },
    { name: 'C++', mode: 'cpp', ext: ['cpp', 'cc', 'cxx', 'hpp', 'hh', 'hxx'] },
    { name: 'C#', mode: 'csharp', ext: ['cs'] },
    { name: 'Conf', mode: 'ini', ext: ['conf'] },
    { name: 'CSS', mode: 'css', ext: ['css'] },
    { name: 'Diff', mode: 'diff', ext: ['diff', 'patch'] },
    { name: 'Dockerfile', mode: 'dockerfile', file: /^Dockerfile$/ },
    { name: 'Go', mode: 'go', ext: ['go'] },
    { name: 'HTML', mode: 'html', ext: ['html', 'htm', 'hbs'] },
    { name: 'JavaScript', mode: 'javascript', ext: ['js'], alias: ['node'] },
    { name: 'TypeScript', mode: 'typescript', ext: ['ts'] },
    { name: 'JSON', mode: 'json', ext: ['json', 'map'] },
    { name: 'Lua', mode: 'lua', ext: ['lua'] },
    { name: 'Markdown', mode: 'markdown', ext: ['md', 'markdown'] },
    { name: 'PHP', mode: 'php', ext: ['php', 'phtml'] },
    { name: 'Plain Text', mode: 'plaintext', ext: ['txt', 'text', 'conf', 'log'] },
    { name: 'Python', mode: 'python', ext: ['py', 'pyw'], file: /^(BUCK|BUILD)$/ },
    { name: 'Ruby', mode: 'ruby', ext: ['rb'] },
    { name: 'Rust', mode: 'rust', ext: ['rs'] },
    { name: 'Shell', mode: 'shell', ext: ['sh', 'bash', 'zsh'], file: /^PKGBUILD$/ },
    { name: 'SQL', mode: 'sql', ext: ['sql'] },
    { name: 'TOML', mode: 'hcl', ext: ['toml'] },
    { name: 'Vue', mode: 'vue', ext: ['vue'] },
    { name: 'XML', mode: 'xml', ext: ['xml', 'svg', 'xsl', 'xsd'] },
    { name: 'YAML', mode: 'yaml', ext: ['yaml', 'yml'] },
];

export default modes;
