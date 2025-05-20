import { useState } from "react";
import { ChevronDownIcon, ChevronUpIcon } from "@heroicons/react/24/outline";

export default function CollapsibleSection({ title, children }) {
    const [open, setOpen] = useState(true);

    return (
        <div className="bg-gray-800 rounded-lg border border-gray-700 shadow my-4">
            <button
                onClick={() => setOpen(!open)}
                className="w-full flex justify-between items-center px-6 py-4 text-left bg-gray-700 hover:bg-gray-600 transition rounded-t-lg"
            >
                <h3 className="text-xl font-semibold">{title}</h3>
                {open ? (
                    <ChevronUpIcon className="h-5 w-5 text-gray-300" />
                ) : (
                    <ChevronDownIcon className="h-5 w-5 text-gray-300" />
                )}
            </button>
            {open && (
                <div className="px-6 py-5 space-y-4">
                    {children}
                </div>
            )}
        </div>
    );
}
