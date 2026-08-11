const API_BASE = (process.env.NEXT_PUBLIC_API_URL || "/api").replace(/\/$/, "");

export type ApiUser = {
  id: number;
  name: string;
  email: string;
};

async function request<T>(
  route: string,
  options: RequestInit = {},
  query: Record<string, string | number | undefined> = {},
): Promise<T> {
  const method = (options.method || "GET").toUpperCase();
  const headers = new Headers(options.headers || {});
  if (method !== "GET" && method !== "HEAD" && !headers.has("Content-Type")) {
    headers.set("Content-Type", "application/json");
  }

  const params = new URLSearchParams();
  params.set("r", route);
  for (const [k, v] of Object.entries(query)) {
    if (v !== undefined && v !== "") params.set(k, String(v));
  }

  const res = await fetch(`${API_BASE}/index.php?${params.toString()}`, {
    ...options,
    method,
    headers,
    credentials: "include",
    cache: "no-store",
  });

  const data = await res.json().catch(() => ({
    ok: false,
    error: "Réponse invalide du serveur",
  }));

  if (!res.ok || data.ok === false) {
    throw new Error(data.error || `Erreur ${res.status}`);
  }

  return data as T;
}

export type Client = {
  id: number;
  company?: string | null;
  first_name: string;
  last_name: string;
  email?: string | null;
  phone?: string | null;
  address?: string | null;
  city?: string | null;
  postal_code?: string | null;
  notes?: string | null;
  status: string;
};

export type LineItem = {
  id?: number;
  description: string;
  quantity: number;
  unit_price: number;
  line_total?: number;
};

export type Devis = {
  id: number;
  number: string;
  client_id: number;
  title: string;
  status: string;
  issue_date: string;
  valid_until?: string | null;
  notes?: string | null;
  subtotal: number;
  tax_rate: number;
  tax_amount: number;
  total: number;
  first_name?: string;
  last_name?: string;
  company?: string | null;
};

export type Facture = Devis & {
  due_date?: string | null;
  amount_paid?: number;
  devis_id?: number | null;
  paid_at?: string | null;
};

export type Paiement = {
  id: number;
  facture_id: number;
  amount: number;
  paid_at: string;
  method: string;
  reference?: string | null;
  facture_number?: string;
  first_name?: string;
  last_name?: string;
  company?: string | null;
};

export type DocRow = {
  id: number;
  number: string;
  total: number;
  status: string;
  title?: string;
};

export const api = {
  me: () => request<{ user: ApiUser }>("auth/me"),
  login: (email: string, password: string) =>
    request<{ user: ApiUser }>("auth/login", {
      method: "POST",
      body: JSON.stringify({ email, password }),
    }),
  logout: () => request("auth/logout", { method: "POST", body: "{}" }),
  dashboard: () =>
    request<{
      stats: {
        clients: number;
        ca_month: number;
        unpaid: number;
        devis_open: number;
        factures_late: number;
      };
      monthly: Record<string, number>;
      recent_clients: Client[];
      recent_factures: Facture[];
    }>("dashboard"),
  clients: {
    list: (q = "", status = "") =>
      request<{ clients: Client[] }>("clients", {}, { q, status }),
    get: (id: number) =>
      request<{ client: Client; devis: DocRow[]; factures: DocRow[] }>(
        `clients/${id}`,
      ),
    create: (body: Partial<Client>) =>
      request<{ client: Client }>("clients", {
        method: "POST",
        body: JSON.stringify(body),
      }),
    update: (id: number, body: Partial<Client>) =>
      request<{ client: Client }>(`clients/${id}`, {
        method: "PUT",
        body: JSON.stringify(body),
      }),
    remove: (id: number) => request(`clients/${id}`, { method: "DELETE" }),
  },
  devis: {
    list: (status = "") =>
      request<{ devis: Devis[] }>("devis", {}, { status }),
    get: (id: number) =>
      request<{ devis: Devis; items: LineItem[] }>(`devis/${id}`),
    create: (body: unknown) =>
      request<{ devis: Devis }>("devis", {
        method: "POST",
        body: JSON.stringify(body),
      }),
    update: (id: number, body: unknown) =>
      request<{ devis: Devis }>(`devis/${id}`, {
        method: "PUT",
        body: JSON.stringify(body),
      }),
    remove: (id: number) => request(`devis/${id}`, { method: "DELETE" }),
    toFacture: (id: number) =>
      request<{ facture: Facture }>(`devis/${id}/to-facture`, {
        method: "POST",
        body: "{}",
      }),
  },
  factures: {
    list: (status = "") =>
      request<{ factures: Facture[] }>("factures", {}, { status }),
    get: (id: number) =>
      request<{ facture: Facture; items: LineItem[]; paiements: Paiement[] }>(
        `factures/${id}`,
      ),
    create: (body: unknown) =>
      request<{ facture: Facture }>("factures", {
        method: "POST",
        body: JSON.stringify(body),
      }),
    update: (id: number, body: unknown) =>
      request<{ facture: Facture }>(`factures/${id}`, {
        method: "PUT",
        body: JSON.stringify(body),
      }),
    remove: (id: number) => request(`factures/${id}`, { method: "DELETE" }),
    addPayment: (id: number, body: unknown) =>
      request(`factures/${id}/paiements`, {
        method: "POST",
        body: JSON.stringify(body),
      }),
    deletePayment: (paymentId: number) =>
      request(`paiements/${paymentId}`, { method: "DELETE" }),
  },
  compta: (from: string, to: string, year: number) =>
    request<{
      from: string;
      to: string;
      year: number;
      stats: {
        encaisse: number;
        facture: number;
        due: number;
      };
      monthly: Record<string, number>;
      paiements: Paiement[];
    }>("compta", {}, { from, to, year }),
};

export function money(n: number | string) {
  return `$${Number(n).toLocaleString("fr-FR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
}

export function clientName(c: {
  first_name?: string;
  last_name?: string;
  company?: string | null;
}) {
  const name = `${c.first_name || ""} ${c.last_name || ""}`.trim();
  return c.company ? `${c.company} — ${name}` : name;
}

export function statusLabel(s: string) {
  const map: Record<string, string> = {
    actif: "Actif",
    prospect: "Prospect",
    archive: "Archivé",
    brouillon: "Brouillon",
    envoye: "Envoyé",
    envoyee: "Envoyée",
    accepte: "Accepté",
    refuse: "Refusé",
    expire: "Expiré",
    payee: "Payée",
    en_retard: "En retard",
    annulee: "Annulée",
  };
  return map[s] || s;
}
