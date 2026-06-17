import { route as ziggyRoute } from "ziggy-js";
import { Ziggy } from "../ziggy";

export default function route(name, params, absolute = false) {
  return ziggyRoute(name, params, absolute, Ziggy);
}
