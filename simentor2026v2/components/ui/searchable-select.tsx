import * as React from "react"
import { Check, ChevronsUpDown, Search } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

type SearchableOption = {
  value: string
  label: string
}

type SearchableSelectProps = {
  value: string
  onChange: (value: string) => void
  options: SearchableOption[]
  placeholder?: string
  searchPlaceholder?: string
  emptyLabel?: string
  className?: string
  disabled?: boolean
  wrapLabel?: boolean
}

export function SearchableSelect({
  value,
  onChange,
  options,
  placeholder = "Pilih opsi",
  searchPlaceholder = "Cari...",
  emptyLabel = "Tidak ada data.",
  className,
  disabled = false,
  wrapLabel = false,
}: SearchableSelectProps) {
  const [open, setOpen] = React.useState(false)
  const [query, setQuery] = React.useState("")

  const selected = React.useMemo(
    () => options.find((option) => option.value === value) ?? null,
    [options, value]
  )

  const filteredOptions = React.useMemo(() => {
    if (!query.trim()) return options
    const normalized = query.toLowerCase()
    return options.filter((option) =>
      option.label.toLowerCase().includes(normalized)
    )
  }, [options, query])

  React.useEffect(() => {
    if (!open) {
      setQuery("")
    }
  }, [open])

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger
        render={
          <Button
            type="button"
            variant="outline"
            className={cn(
              "h-9 w-full justify-between rounded-xl border-white/50 bg-white/70 px-3 text-xs font-normal text-gray-700 shadow-none hover:bg-white dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10",
              wrapLabel && "h-auto min-h-9 items-start py-2",
              className
            )}
            disabled={disabled}
          >
            <span
              className={cn(
                "truncate text-left",
                wrapLabel && "whitespace-normal break-words leading-snug"
              )}
            >
              {selected?.label ?? placeholder}
            </span>
            <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
          </Button>
        }
      />
      <PopoverContent
        align="start"
        className="w-[var(--anchor-width)] min-w-[220px] rounded-xl border border-white/40 bg-white/95 p-2 text-gray-900 shadow-xl backdrop-blur dark:border-white/10 dark:bg-[#111827]/95 dark:text-white"
      >
        <div className="relative mb-2">
          <Search className="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" />
          <Input
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder={searchPlaceholder}
            className="h-9 rounded-lg border-slate-200 bg-white pl-8 text-xs dark:border-white/10 dark:bg-slate-950"
          />
        </div>
        <div className="max-h-64 overflow-y-auto">
          {filteredOptions.length === 0 ? (
            <div className="px-2 py-3 text-xs text-gray-500 dark:text-gray-400">
              {emptyLabel}
            </div>
          ) : (
            <div className="space-y-1">
              {filteredOptions.map((option) => {
                const isSelected = option.value === value

                return (
                  <button
                    key={option.value}
                    type="button"
                    onClick={() => {
                      onChange(option.value)
                      setOpen(false)
                    }}
                    className={cn(
                      "flex w-full items-center justify-between rounded-lg px-2.5 py-2 text-left text-xs transition-colors",
                      isSelected
                        ? "bg-slate-900 text-white dark:bg-white dark:text-slate-900"
                        : "text-gray-700 hover:bg-slate-100 dark:text-gray-200 dark:hover:bg-white/10"
                    )}
                  >
                    <span
                      className={cn(
                        "truncate",
                        wrapLabel && "whitespace-normal break-words leading-snug"
                      )}
                    >
                      {option.label}
                    </span>
                    <Check
                      className={cn(
                        "ml-2 h-3.5 w-3.5 shrink-0",
                        isSelected ? "opacity-100" : "opacity-0"
                      )}
                    />
                  </button>
                )
              })}
            </div>
          )}
        </div>
      </PopoverContent>
    </Popover>
  )
}
