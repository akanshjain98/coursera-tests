import { useCallback, useEffect, useMemo, useRef, useState } from "react";

interface CustomTimeDropdownProps {
  label: string;
  name: string;
  value: string; // expected in 24-hour format: "13:45"
  onChange: (time: string) => void; // should return 24-hour format
}

const generateOptions = (limit: number) => {
  return Array.from({ length: limit }, (_, i) => i.toString().padStart(2, "0"));
};

const hours12 = generateOptions(12).map(h => h === "00" ? "12" : h); // "01" to "12"
const minutes = generateOptions(60);

function convertTo12Hour(time: string): { hour: string; minute: string; period: "AM" | "PM" } {
    console.log(time);
    
  const [h, m] = time.split(":").map(Number);
  const period = h >= 12 ? "PM" : "AM";
  const hour = ((h % 12) || 12).toString().padStart(2, "0");
  const minute = m.toString().padStart(2, "0");
  return { hour, minute, period };
}

function convertTo24Hour(hour: string, minute: string, period: "AM" | "PM"): string {
  let h = parseInt(hour, 10);
  if (period === "PM" && h < 12) h += 12;
  if (period === "AM" && h === 12) h = 0;
  return `${h.toString().padStart(2, "0")}:${minute}`;
}

export default function CustomTimeDropdown({ label, name, value, onChange }: CustomTimeDropdownProps) {
    const { hour, minute, period } = convertTo12Hour(value);
    const [selectedHour, setSelectedHour] = useState(hour);
    const [selectedMinute, setSelectedMinute] = useState(minute);
    const [selectedPeriod, setSelectedPeriod] = useState<"AM" | "PM">(period);
    const [isOpen, setIsOpen] = useState(false);
  
    const wrapperRef = useRef<HTMLDivElement>(null);
  
    // Memoize options to prevent unnecessary recalculations
    const hourOptions = useMemo(() => hours12, []);
    const minuteOptions = useMemo(() => minutes, []);
    const periodOptions = useMemo(() => ["AM", "PM"] as const, []);
  
    const handleSelect = useCallback((h: string, m: string, p: "AM" | "PM") => {
      onChange(convertTo24Hour(h, m, p));
      setIsOpen(false);
    }, [onChange]);
  
    // Close dropdown when clicking outside
    useEffect(() => {
      const handleClickOutside = (event: MouseEvent) => {
        if (wrapperRef.current && !wrapperRef.current.contains(event.target as Node)) {
          setIsOpen(false);
        }
      };
      document.addEventListener("mousedown", handleClickOutside);
      return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);
  
    // Update internal state when value prop changes
    useEffect(() => {
      const { hour, minute, period } = convertTo12Hour(value);
      setSelectedHour(hour);
      setSelectedMinute(minute);
      setSelectedPeriod(period);
    }, [value]);
  
    return (
      <div ref={wrapperRef} className="position-relative mb-3">
        <label htmlFor={name} className="fw-bold">
          {label}
        </label>
        <div
          id={name}
          role="combobox"
          aria-expanded={isOpen}
          aria-controls={`${name}-dropdown`}
          aria-label={`Select time, currently ${selectedHour}:${selectedMinute} ${selectedPeriod}`}
          className="form-control d-flex justify-content-between align-items-center"
          style={{
            backgroundColor: "#e2e1e1",
            fontSize: "16px",
            fontWeight: "bold",
            cursor: "pointer",
            width: "160px",
          }}
          onClick={() => setIsOpen(!isOpen)}
          onKeyDown={(e) => {
            if (e.key === "Enter" || e.key === " ") {
              e.preventDefault();
              setIsOpen(!isOpen);
            }
          }}
          tabIndex={0}
        >
          {selectedHour}:{selectedMinute} {selectedPeriod}
          <i className={`bi bi-chevron-${isOpen ? "up" : "down"} ms-2`}></i>
        </div>
  
        {isOpen && (
          <div
            id={`${name}-dropdown`}
            role="listbox"
            className="position-absolute bg-white shadow rounded p-2 d-flex gap-2"
            style={{
              top: "60px",
              zIndex: 10,
              width: "160px",
              border: "1px solid #ccc",
              maxHeight: "200px",
              overflowY: "auto",
              overscrollBehavior: "contain",
            }}
          >
            <div style={{ maxHeight: "80px", overflowY: "auto" }}>
              {hourOptions.map((h) => (
                <div
                  key={h}
                  role="option"
                  aria-selected={h === selectedHour}
                  className={`px-2 py-1 ${
                    h === selectedHour ? "bg-primary text-white" : "text-dark"
                  } rounded pointer`}
                  onClick={() => {
                    setSelectedHour(h);
                    handleSelect(h, selectedMinute, selectedPeriod);
                  }}
                >
                  {h}
                </div>
              ))}
            </div>
  
            <div style={{ maxHeight: "80px", overflowY: "auto" }}>
              {minuteOptions.map((m) => (
                <div
                  key={m}
                  role="option"
                  aria-selected={m === selectedMinute}
                  className={`px-2 py-1 ${
                    m === selectedMinute ? "bg-primary text-white" : "text-dark"
                  } rounded pointer`}
                  onClick={() => {
                    setSelectedMinute(m);
                    handleSelect(selectedHour, m, selectedPeriod);
                  }}
                >
                  {m}
                </div>
              ))}
            </div>
  
            <div className="d-flex flex-column gap-1">
              {periodOptions.map((p) => (
                <div
                  key={p}
                  role="option"
                  aria-selected={p === selectedPeriod}
                  className={`px-2 py-1 ${
                    p === selectedPeriod ? "bg-danger text-white" : "text-dark"
                  } rounded pointer`}
                  onClick={() => {
                    setSelectedPeriod(p);
                    handleSelect(selectedHour, selectedMinute, p);
                  }}
                >
                  {p}
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    );
  }
