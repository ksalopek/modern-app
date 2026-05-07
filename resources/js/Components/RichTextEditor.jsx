import React, { useEffect } from 'react';
import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';

// Toolbar component for the editor
const MenuBar = ({ editor }) => {
    if (!editor) {
        return null;
    }

    // Define a reusable button component to avoid repetition
    const EditorButton = ({ onClick, disabled, isActive, children }) => (
        <button
            type="button" // <-- CRITICAL FIX: Prevents form submission
            onClick={onClick}
            disabled={disabled}
            className={`px-2 py-1 rounded-md text-sm transition-colors ${
                isActive ? 'bg-indigo-600 text-white' : 'bg-gray-200 hover:bg-gray-300'
            }`}
        >
            {children}
        </button>
    );

    return (
        <div className="flex flex-wrap gap-1 p-2 border-b border-gray-300 bg-gray-50 rounded-t-md">
            <EditorButton onClick={() => editor.chain().focus().toggleBold().run()} disabled={!editor.can().chain().focus().toggleBold().run()} isActive={editor.isActive('bold')}>Bold</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().toggleItalic().run()} disabled={!editor.can().chain().focus().toggleItalic().run()} isActive={editor.isActive('italic')}>Italic</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().toggleStrike().run()} disabled={!editor.can().chain().focus().toggleStrike().run()} isActive={editor.isActive('strike')}>Strike</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().toggleCode().run()} disabled={!editor.can().chain().focus().toggleCode().run()} isActive={editor.isActive('code')}>Code</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().setParagraph().run()} isActive={editor.isActive('paragraph')}>Paragraph</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().toggleHeading({ level: 1 }).run()} isActive={editor.isActive('heading', { level: 1 })}>H1</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()} isActive={editor.isActive('heading', { level: 2 })}>H2</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()} isActive={editor.isActive('heading', { level: 3 })}>H3</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().toggleBulletList().run()} isActive={editor.isActive('bulletList')}>Bullet List</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().toggleOrderedList().run()} isActive={editor.isActive('orderedList')}>Ordered List</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().toggleCodeBlock().run()} isActive={editor.isActive('codeBlock')}>Code Block</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().toggleBlockquote().run()} isActive={editor.isActive('blockquote')}>Blockquote</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().setHorizontalRule().run()}>HR</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().setHardBreak().run()}>Hard Break</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().undo().run()} disabled={!editor.can().chain().focus().undo().run()}>Undo</EditorButton>
            <EditorButton onClick={() => editor.chain().focus().redo().run()} disabled={!editor.can().chain().focus().redo().run()}>Redo</EditorButton>
            <EditorButton
                onClick={() => {
                    const url = window.prompt('URL');
                    if (url) {
                        editor.chain().focus().setLink({ href: url }).run();
                    }
                }}
                isActive={editor.isActive('link')}
            >
                Set Link
            </EditorButton>
            <EditorButton onClick={() => editor.chain().focus().unsetLink().run()} disabled={!editor.isActive('link')}>Unset Link</EditorButton>
        </div>
    );
};

export default function RichTextEditor({ content, onChange, className = '' }) {
    const editor = useEditor({
        extensions: [
            StarterKit,
            Link.configure({
                openOnClick: false,
                autolink: true,
                linkOnPaste: true,
            }),
        ],
        content: content,
        onUpdate: ({ editor }) => {
            onChange(editor.getHTML());
        },
        editorProps: {
            attributes: {
                class: 'prose max-w-none focus:outline-none p-4 min-h-[150px]',
            },
        },
    });

    useEffect(() => {
        if (editor && editor.getHTML() !== content) {
            editor.commands.setContent(content, false);
        }
    }, [content, editor]);

    return (
        <div className={`border border-gray-300 rounded-md shadow-sm ${className}`}>
            <MenuBar editor={editor} />
            <EditorContent editor={editor} />
        </div>
    );
}
