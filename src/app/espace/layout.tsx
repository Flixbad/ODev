import { type ReactNode } from "react";
import { EspaceShell } from "@/components/crm/EspaceShell";

export default function EspaceLayout({ children }: { children: ReactNode }) {
  return <EspaceShell>{children}</EspaceShell>;
}
